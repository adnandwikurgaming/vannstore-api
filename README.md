# VannStore API

Backend REST API untuk **VannStore – Marketplace Produk Handmade**.
Dipakai oleh aplikasi Flutter Android (pembeli & penjual) dan panel web admin.

- Laravel 13 + Laravel Sanctum (token Bearer)
- MySQL (produksi/pengembangan), SQLite in-memory (test)
- Semua endpoint di bawah prefix `/api/v1`, respons JSON

Proyek kelompok mata kuliah Sistem Web dan Seluler Lanjut. Pembagian tugas dan keputusan desain: [docs/PEMBAGIAN_TUGAS.md](docs/PEMBAGIAN_TUGAS.md).

---

## Kebutuhan

- PHP **8.3+** dengan ekstensi `openssl`, `pdo_mysql`, `mbstring`, `fileinfo` (untuk test: `pdo_sqlite`, `sqlite3`)
- Composer 2
- MySQL 8 / MariaDB

## Cara menjalankan

```bash
git clone https://github.com/adnandwikurgaming/vannstore-api.git
cd vannstore-api
composer install
cp .env.example .env
php artisan key:generate
```

Buat database `vannstore` di MySQL, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vannstore
DB_USERNAME=root
DB_PASSWORD=
```

Isi tabel dan data contoh, lalu jalankan server:

```bash
php artisan migrate:fresh --seed
php artisan serve
```

API tersedia di `http://127.0.0.1:8000/api/v1`.
Untuk diakses dari emulator Android gunakan `http://10.0.2.2:8000/api/v1`; dari HP fisik jalankan `php artisan serve --host=0.0.0.0` dan pakai IP laptop.

**Tanpa MySQL (coba cepat):** di `.env` set `DB_CONNECTION=sqlite`, hapus baris `DB_HOST` sampai `DB_PASSWORD`, buat file kosong `database/database.sqlite`, lalu `php artisan migrate:fresh --seed`.

## Akun seeder

Semua akun memakai password **`password`**.

| Peran | Email | Keterangan |
|---|---|---|
| Admin | `admin@vannstore.test` | |
| Penjual | `rina@vannstore.test` | Toko "Rajut Rina" |
| Penjual | `bagas@vannstore.test` | Toko "Kayu Bagas Workshop" |
| Pembeli | `andi@vannstore.test` | |
| Pembeli | `sari@vannstore.test` | |

Data uji khusus: produk **Gelang Manik Etnik** stok 1 (uji stok terakhir) dan **Miniatur Vespa Kayu** stok 0 (uji produk habis).

## Autentikasi

```http
POST /api/v1/login
Content-Type: application/json
Accept: application/json

{"email": "andi@vannstore.test", "password": "password"}
```

```json
{
  "message": "Login berhasil.",
  "data": {
    "token": "1|xxxxxxxx",
    "user": { "id": 4, "name": "Andi Saputra", "role": "pembeli", "store_name": null }
  }
}
```

Request berikutnya wajib membawa header:

```http
Authorization: Bearer 1|xxxxxxxx
Accept: application/json
```

## Endpoint

| Method | Endpoint | Peran | Keterangan |
|---|---|---|---|
| POST | `/register` | Publik | Daftar sebagai `pembeli` atau `penjual` (admin tidak bisa daftar) |
| POST | `/login` | Publik | Maks. 5 percobaan/menit |
| POST | `/logout` | Semua | Mencabut token aktif (204) |
| GET | `/user` | Semua | Data user yang sedang login |
| POST | `/orders` | Pembeli | Checkout keranjang; dipecah 1 pesanan per penjual |
| GET | `/orders/me` | Pembeli | Riwayat pesanan sendiri |
| GET | `/orders/{order}` | Pembeli/penjual terkait, admin | Detail pesanan |
| GET | `/recommendations` | Semua | Maks. 10 rekomendasi produk |
| GET | `/seller/orders?status=` | Penjual | Pesanan masuk ke toko |
| PATCH | `/seller/orders/{order}/status` | Penjual pemilik | Ubah status pesanan |

### Contoh checkout

```http
POST /api/v1/orders

{
  "items": [
    { "product_id": 1, "qty": 2 },
    { "product_id": 6, "qty": 1 }
  ],
  "shipping_address": "Jl. Merdeka No. 10, Bandung",
  "notes": "Tolong dibungkus kado"
}
```

Respons `201` berisi array pesanan (satu per penjual). Stok dipotong dalam satu transaksi dengan penguncian baris, sehingga dua pembeli tidak bisa sama-sama mengambil stok terakhir.

### Alur status pesanan

```
menunggu_konfirmasi → diproses → dikirim → selesai
        └──────────────┴──→ dibatalkan (stok dikembalikan)
```

Setiap respons pesanan menyertakan `allowed_next_status`. Perubahan status menyimpan notifikasi untuk pembeli (tabel `notifications`).

### Rekomendasi

Kategori diberi skor dari produk yang dilihat (×1) dan dibeli (×3) dalam 90 hari terakhir; produk dari kategori teratas ditampilkan lebih dulu. Tanpa riwayat, sistem menampilkan produk terlaris lalu terbaru. Produk nonaktif, stok habis, dan milik sendiri tidak ditampilkan.

## Format galat

Semua galat berbentuk `{"message": "...", "errors": {...}}`.

| Kode | Kapan | Contoh |
|---|---|---|
| 401 | Tanpa token / token dicabut / login salah | `{"message": "Unauthenticated."}` |
| 403 | Peran atau pemilik tidak berhak | `{"message": "Anda tidak memiliki akses"}` |
| 404 | Data atau route tidak ada | `{"message": "Data tidak ditemukan"}` |
| 409 | Stok tidak cukup / produk tidak tersedia | `{"message": "Stok tidak mencukupi", "errors": {"items.1.qty": ["Stok Gelang Manik Etnik tersisa 1, diminta 5."]}}` |
| 422 | Input tidak valid / transisi status salah | `{"message": "Perubahan status tidak diizinkan", "errors": {"status": ["..."]}}` |
| 429 | Terlalu banyak percobaan login | `{"message": "Too Many Attempts."}` |

## Pengujian

```bash
php artisan test
```

Test memakai SQLite in-memory (`phpunit.xml`), jadi tidak menyentuh database MySQL. Di Windows, aktifkan dulu `extension=pdo_sqlite` dan `extension=sqlite3` di `php.ini`.

CI GitHub Actions ([.github/workflows/tests.yml](.github/workflows/tests.yml)) menjalankan seluruh test pada setiap push dan pull request ke `main`/`develop`.

## Produksi

```env
APP_ENV=production
APP_DEBUG=false
```

`APP_DEBUG` **wajib `false`** di server agar stack trace dan path server tidak bocor ke klien. Jangan pernah meng-commit file `.env`.

## Struktur penting

```
app/
  Enums/            UserRole, OrderStatus (aturan transisi)
  Exceptions/       InsufficientStockException (409), InvalidStatusTransitionException (422)
  Http/Controllers/ AuthController, Api/V1/OrderController, Api/V1/Seller/OrderController, Api/V1/RecommendationController
  Http/Requests/    validasi input (Form Request)
  Http/Resources/   bentuk respons JSON
  Policies/         OrderPolicy, ProductPolicy (RBAC + otorisasi objek)
  Services/         OrderService, RecommendationService (logika bisnis)
database/
  migrations/  factories/  seeders/
routes/api.php
tests/Feature/
```

## Alur kerja Git

- `main`: rilis (tag `v0.3.0`)
- `develop`: integrasi, harus lolos test
- `feature/*`: satu fitur per branch, masuk lewat pull request yang di-review anggota lain
- Commit mengikuti Conventional Commits: `feat`, `fix`, `test`, `docs`, `ci`, `chore`
