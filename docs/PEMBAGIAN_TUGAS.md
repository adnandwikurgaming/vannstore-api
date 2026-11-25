# Pembagian Tugas Backend v1 — VannStore API

Tugas Pertemuan 10/11 Sistem Web dan Seluler Lanjut: **Implementasi Backend, REST API, dan Layanan Sistem**.
Backend Laravel 13 + Sanctum ini nanti dipakai aplikasi Flutter di pertemuan berikutnya, jadi repositorinya tetap sama sampai presentasi akhir.

---

## 1. Yang harus dikumpulkan

**A. Repositori GitHub**
- Tag `v0.3.0` di branch `main`
- README: cara menjalankan + daftar akun seeder
- Branch per fitur, **setiap anggota minimal 1 PR yang di-review anggota lain**
- CI GitHub Actions menjalankan `php artisan test` dan lolos (**minimal 8 feature test**)

**B. Dokumen PDF** — `SWSL11_Kel<no>_VannStore_Backend.pdf`
Bab "Implementasi Backend" ditambahkan ke dokumen proyek, isinya:
- Pemetaan rancangan → berkas (lihat tabel di bagian 4)
- Potongan kode inti
- Tabel endpoint aktual
- Tabel kode galat
- Bukti uji: screenshot `php artisan test` + koleksi Postman (request sukses dan galat)
- Tabel kontribusi anggota

Diunggah ketua kelompok **sebelum pertemuan berikutnya**.

### Syarat minimum dari dosen
| # | Syarat | Status |
|---|---|---|
| 1 | Migration, model, relasi, seeder seluruh entitas ERD (min. 4 tabel) | ✅ Selesai (6 tabel + notifications) |
| 2 | Login/logout Sanctum + min. 6 endpoint `/api/v1` | 🟡 6 endpoint pesanan selesai, auth belum |
| 3 | Form Request tiap endpoint yang menerima input; API Resource untuk respons | 🟡 Pesanan selesai, sisanya belum |
| 4 | Min. 1 service class berisi aturan bisnis (dengan transaksi) | ✅ `OrderService`, `RecommendationService` |
| 5 | Policy sesuai matriks RBAC, termasuk otorisasi tingkat objek | ⬜ Belum |
| 6 | Galat JSON seragam (401, 403, 404, 422 + 1 galat bisnis) + ≥ 8 feature test | 🟡 409 & 422 bisnis selesai, handler & test belum |
| 7 | Git: branch per fitur, ≥ 1 PR ter-review per anggota, CI, README | 🟡 Repo & 2 branch fitur ada, belum di GitHub |

### Rubrik penilaian
| Aspek | Bobot | Penanggung jawab utama |
|---|---|---|
| Integrasi basis data | 15 | Anggota 1 |
| RESTful API | 20 | Anggota 1 + Anggota 2 |
| Service logic | 15 | Anggota 1 |
| Validasi & otorisasi | 15 | Anggota 2 + Anggota 3 |
| Error handling | 10 | Anggota 3 |
| Pengujian | 10 | Anggota 4 |
| Kolaborasi Git | 10 | Semua |
| Dokumen & README | 5 | Anggota 4 |

---

## 2. Pembagian tugas

| Anggota | Nama | Bagian | Branch |
|---|---|---|---|
| Anggota 1 | [Nama] | Setup proyek, basis data, alur pesanan, service, rekomendasi | `feature/data-model`, `feature/order-api` |
| Anggota 2 | [Nama] | Autentikasi, katalog, produk penjual, kategori admin | `feature/auth-api`, `feature/product-api` |
| Anggota 3 | [Nama] | Policy RBAC + error handling | `feature/policy`, `feature/error-handling` |
| Anggota 4 | [Nama] | Feature test, CI, README, Postman, dokumen PDF | `feature/tests`, `ci/github-actions`, `docs/readme` |

### Anggota 1 — ✅ selesai
- [x] Proyek Laravel 13 + Sanctum (`php artisan install:api`), `.env.example` MySQL, timezone `Asia/Jakarta`
- [x] Migration 6 tabel + `notifications`, model + relasi, enum `UserRole` & `OrderStatus`
- [x] Factory per peran (`pembeli()`, `penjual()`, `admin()`), `ProductFactory`, `OrderFactory`, `CategoryFactory`
- [x] Seeder: 1 admin, 2 penjual, 2 pembeli, 5 kategori, 10 produk
- [x] `OrderService`: checkout dalam transaksi + `lockForUpdate`, pecah pesanan per penjual, stok kurang → **409**, transisi status salah → **422**, pembatalan mengembalikan stok, notifikasi ke pembeli
- [x] `RecommendationService`: maks. 10 produk dari kategori yang paling sering dilihat/dibeli, fallback produk populer/terbaru
- [x] Endpoint pesanan + rekomendasi (lihat bagian 3), `StoreOrderRequest`, `UpdateOrderStatusRequest`
- [x] API Resource: `ProductResource`, `CategoryResource`, `SellerResource`, `OrderResource`, `OrderItemResource` (**pakai ulang** resource ini, jangan buat baru)
- [ ] Push ke GitHub + buka PR `feature/data-model` dan `feature/order-api` ke `develop`
- [ ] Review PR anggota lain

### Anggota 2 — Autentikasi & katalog
Branch dibuat dari `develop` **setelah PR data-model di-merge**.

- [ ] `AuthController`: `POST /register`, `POST /login` (middleware `throttle:5,1`), `POST /logout`
  - Login pakai `email` + `password`, respons `{"data": {"token": "...", "user": {id, name, role}}}`
  - Register: role hanya boleh `pembeli` atau `penjual` (admin tidak bisa daftar sendiri); `store_name` wajib bila penjual
  - Logout menghapus token aktif → 204
- [ ] `ProductController` (katalog pembeli):
  - `GET /products?q=&category_id=` → hanya produk `active()`, paginate 15, pakai scope `search()` yang sudah ada di model
  - `GET /products/{product}` → produk nonaktif balas 404; **setiap kali dibuka, simpan `ProductView`** (`user_id`, `product_id`) — ini sumber data rekomendasi
  - `GET /categories`
- [ ] `Seller\ProductController`: `GET /seller/products`, `POST /seller/products`, `PATCH /seller/products/{product}`, `PATCH /seller/products/{product}/stock`
  - Upload foto pakai disk `public` (`photo_path`), jalankan `php artisan storage:link`
  - Menonaktifkan produk = `is_active: false` (tidak ada hapus permanen, karena produk yang pernah dipesan tidak boleh dihapus)
- [ ] `Admin\CategoryController`: `GET /admin/categories`, `POST /admin/categories`
- [ ] `PATCH /admin/products/{product}/deactivate`
- [ ] Form Request untuk semua endpoint di atas yang menerima input (`LoginRequest`, `RegisterRequest`, `StoreProductRequest`, `UpdateProductRequest`, `UpdateStockRequest`, `StoreCategoryRequest`)
- [ ] Tambahkan route di slot yang sudah diberi komentar di `routes/api.php`

### Anggota 3 — Policy & error handling
- [ ] **`OrderPolicy` (prioritas, karena route pesanan sudah memakainya):**
  - `create(User $user)` → hanya `pembeli`
  - `view(User $user, Order $order)` → pembeli pemilik pesanan, penjual pesanan itu, atau admin
  - `updateStatus(User $user, Order $order)` → hanya penjual pemilik pesanan (`$order->seller_id === $user->id`)
  - Selama policy ini belum ada, `POST /orders`, `GET /orders/{order}`, dan `PATCH /seller/orders/{order}/status` **selalu 403**
- [ ] `ProductPolicy`: `create` hanya penjual; `update`/`updateStock` hanya penjual pemilik produk; `deactivate` hanya admin
- [ ] `CategoryPolicy` atau Gate admin untuk `/admin/*`
- [ ] Batasi `/seller/*` hanya untuk role `penjual` (policy atau middleware)
- [ ] Pesan 403 seragam: `{"message": "Anda tidak memiliki akses"}` (atau pesan spesifik dari `Response::deny()`)
- [ ] Handler di `bootstrap/app.php`: 404 → `{"message": "Data tidak ditemukan"}` (jangan bocorkan nama model), 401 tetap `Unauthenticated.`
- [ ] Pastikan `APP_DEBUG=false` di produksi (catat di README)

### Anggota 4 — Test, CI, dokumentasi
- [ ] Aktifkan SQLite di PHP lokal (lihat bagian 7) supaya `php artisan test` jalan
- [ ] Feature test minimal 8, termasuk kasus galat dan batas. Daftar skenario yang **sudah terbukti jalan** dan tinggal dijadikan test:
  1. Checkout 2 produk dari 2 penjual → 201, jadi 2 pesanan, stok terpotong
  2. Pesan melebihi stok → 409, `errors["items.N.qty"]`, stok produk lain tidak ikut terpotong
  3. Pesan produk stok 0 → 409
  4. Penjual memesan produk miliknya sendiri → 409
  5. Ubah status `menunggu_konfirmasi` → `selesai` → 422
  6. `menunggu_konfirmasi` → `diproses` → 200
  7. Dibatalkan → stok kembali, notifikasi tersimpan untuk pembeli
  8. Pesanan yang sudah dibatalkan tidak bisa diubah lagi → 422
  9. Rekomendasi: kategori yang sering dilihat muncul paling atas, maks. 10, tanpa produk nonaktif/habis/milik sendiri
  10. User tanpa riwayat → rekomendasi fallback
  11. Tanpa token → 401
  12. Filter `GET /seller/orders?status=ngawur` → 422
  13. Pembeli A membuka `GET /orders/{id}` milik pembeli B → 403 (setelah policy Anggota 3 masuk)
  14. Penjual mengubah status pesanan toko lain → 403
  15. Login salah → 401; login 6× dalam 1 menit → 429
- [ ] `.github/workflows/tests.yml` (contoh di slide 27). **Catatan:** `composer.lock` dibuat di PHP 8.5, jadi pakai `php-version: '8.5'` atau cek dulu apakah 8.3 lolos
- [ ] README: kebutuhan, cara install, `migrate:fresh --seed`, akun seeder, daftar endpoint
- [ ] Koleksi Postman: request sukses + galat untuk tiap endpoint, export ke `docs/VannStore.postman_collection.json`
- [ ] Susun PDF bab "Implementasi Backend" + tabel kontribusi

---

## 3. Endpoint (`/api/v1`)

Semua endpoint kecuali register/login wajib header `Authorization: Bearer <token>` dan `Accept: application/json`.

| Method | Endpoint | Peran | Pemilik | Status |
|---|---|---|---|---|
| POST | `/register` | Publik | Anggota 2 | ⬜ |
| POST | `/login` | Publik | Anggota 2 | ⬜ |
| POST | `/logout` | Semua | Anggota 2 | ⬜ |
| GET | `/products` | Pembeli | Anggota 2 | ⬜ |
| GET | `/products/{product}` | Pembeli | Anggota 2 | ⬜ |
| GET | `/categories` | Semua | Anggota 2 | ⬜ |
| GET | `/seller/products` | Penjual | Anggota 2 | ⬜ |
| POST | `/seller/products` | Penjual | Anggota 2 | ⬜ |
| PATCH | `/seller/products/{product}` | Penjual | Anggota 2 | ⬜ |
| PATCH | `/seller/products/{product}/stock` | Penjual | Anggota 2 | ⬜ |
| POST | `/orders` | Pembeli | Anggota 1 | ✅ (butuh `OrderPolicy::create`) |
| GET | `/orders/me` | Pembeli | Anggota 1 | ✅ |
| GET | `/orders/{order}` | Pembeli/penjual/admin | Anggota 1 | ✅ (butuh `OrderPolicy::view`) |
| GET | `/seller/orders?status=` | Penjual | Anggota 1 | ✅ |
| PATCH | `/seller/orders/{order}/status` | Penjual | Anggota 1 | ✅ (butuh `OrderPolicy::updateStatus`) |
| GET | `/recommendations` | Pembeli | Anggota 1 | ✅ |
| GET | `/admin/categories` | Admin | Anggota 2 | ⬜ |
| POST | `/admin/categories` | Admin | Anggota 2 | ⬜ |
| PATCH | `/admin/products/{product}/deactivate` | Admin | Anggota 2 | ⬜ |

### Contoh `POST /orders`
Request:
```json
{
  "items": [
    { "product_id": 1, "qty": 2 },
    { "product_id": 6, "qty": 1 }
  ],
  "shipping_address": "Jl. Merdeka No. 10, Bandung",
  "notes": "Tolong dibungkus kado"
}
```
Keranjang disimpan lokal di Flutter; saat checkout, isi keranjang dikirim sekaligus. Server memecah jadi **satu pesanan per penjual** (respons 201 berisi array pesanan).

Respons 409 (stok kurang):
```json
{
  "message": "Stok tidak mencukupi",
  "errors": { "items.1.qty": ["Stok Gelang Manik Etnik tersisa 1, diminta 5."] }
}
```

### Contoh `PATCH /seller/orders/{order}/status`
```json
{ "status": "diproses" }
```
Respons pesanan menyertakan `allowed_next_status` supaya Flutter tahu tombol status apa yang boleh ditampilkan.

---

## 4. Keputusan desain (disepakati)

1. **Alur status pesanan:** `menunggu_konfirmasi → diproses → dikirim → selesai`. `dibatalkan` hanya dari `menunggu_konfirmasi` atau `diproses`; stok dikembalikan saat dibatalkan.
2. **Checkout multi-penjual:** pesanan dipecah satu per penjual.
3. **Keranjang:** disimpan lokal di Flutter, tidak ada tabel/endpoint keranjang.
4. **Stok:** langsung dipotong saat pesanan dibuat (dalam transaksi + lock).
5. **Moderasi produk:** produk penjual langsung tampil; admin bisa menonaktifkan.
6. **Notifikasi (KF-11):** v1 disimpan di tabel `notifications`; FCM ditambahkan di fase Flutter. **Rekomendasi (KF-13):** dikerjakan di v1.
7. **Panel admin:** v1 hanya API admin; tampilan web menyusul.
8. **Endpoint tambahan di luar draf P8:** `POST /logout`, `GET /seller/products`, `GET /orders/{order}`, `PATCH /admin/products/{product}/deactivate`, `GET /categories`.

### Pemetaan rancangan → berkas
| Rancangan | Diwujudkan menjadi | Berkas |
|---|---|---|
| ERD | migration + model + relasi | `database/migrations`, `app/Models` |
| Status pesanan | enum + aturan transisi | `app/Enums/OrderStatus.php` |
| Draf endpoint | route + controller + resource | `routes/api.php`, `app/Http/Controllers/Api/V1`, `app/Http/Resources` |
| Kontrak request | Form Request | `app/Http/Requests` |
| UC-01 Buat Pesanan, KF-08 validasi stok | service + transaksi | `app/Services/OrderService.php` |
| UC-02 Perbarui Status, KF-11 notifikasi | service + notification | `app/Services/OrderService.php`, `app/Notifications/OrderStatusChanged.php` |
| KF-13 rekomendasi | service | `app/Services/RecommendationService.php` |
| Matriks RBAC | policy | `app/Policies` (Anggota 3) |
| Kode galat | exception + handler | `app/Exceptions`, `bootstrap/app.php` |
| Skenario uji (bab 13 P8) | feature test | `tests/Feature` (Anggota 4) |

### ERD
| Tabel | Kolom penting | Relasi |
|---|---|---|
| `users` | `role` (pembeli/penjual/admin), `store_name`, `phone` | 1–N `products` (penjual), 1–N `orders` sebagai pembeli & penjual |
| `categories` | `name`, `slug` | 1–N `products` |
| `products` | `seller_id`, `category_id`, `name`, `description`, `price`, `stock`, `photo_path`, `is_active` | N–1 `users`, N–1 `categories` |
| `orders` | `code`, `buyer_id`, `seller_id`, `status`, `total`, `shipping_address`, `notes` | 1–N `order_items` |
| `order_items` | `order_id`, `product_id`, `product_name`, `price`, `qty`, `subtotal` | harga & nama disimpan saat pesan |
| `product_views` | `user_id`, `product_id`, `viewed_at` | riwayat lihat untuk rekomendasi |

Constraint: kategori yang masih dipakai produk tidak bisa dihapus; produk yang pernah dipesan tidak bisa dihapus (pakai nonaktifkan).

### Peta galat
| Kode | Kapan | Sumber | Status |
|---|---|---|---|
| 401 | Tanpa token / token dicabut | `auth:sanctum` | ✅ bawaan |
| 403 | Peran atau pemilik tidak berhak | Policy | ⬜ Anggota 3 |
| 404 | Data tidak ada | Route model binding + handler | ⬜ Anggota 3 |
| 409 | Stok tidak cukup / produk tidak tersedia | `InsufficientStockException` | ✅ |
| 422 | Input tidak valid / transisi status salah | Form Request, `InvalidStatusTransitionException` | ✅ |
| 429 | Terlalu banyak percobaan login | `throttle:5,1` | ⬜ Anggota 2 |

Semua galat berbentuk `{"message": "...", "errors": {...}}` supaya Flutter cukup punya satu penangan galat.

---

## 5. Alur kerja Git

- `main`: hanya rilis (akhir: tag `v0.3.0`)
- `develop`: integrasi semua fitur, harus lolos test
- `feature/*`: satu fitur, satu anggota, dibuat dari `develop` terbaru

Langkah tiap anggota:
```bash
git checkout develop && git pull
git checkout -b feature/nama-fitur
# kerjakan, commit kecil-kecil
git push -u origin feature/nama-fitur
# buka PR ke develop, minta 1 anggota lain review, merge setelah CI hijau
```

Format commit (Conventional Commits): `feat(lingkup): ...`, `fix(...)`, `test(...)`, `docs(...)`, `ci(...)`, `chore(...)`.

Aturan:
- **Jangan edit migration yang sudah di-merge**; buat migration baru untuk perubahan skema
- **Jangan commit** `.env`, `vendor/`, file `*.sqlite`
- Checklist review: sesuai tabel endpoint & ERD? validasi di Form Request? policy terpasang? kode status & format galat sesuai? ada test sukses & galat?

Urutan merge yang disarankan:
1. `feature/data-model` (Anggota 1) — **paling dulu, semua bergantung ke sini**
2. `feature/order-api` (Anggota 1), `feature/auth-api` (Anggota 2)
3. `feature/policy` (Anggota 3) — membuka akses endpoint pesanan
4. `feature/product-api` (Anggota 2), `feature/error-handling` (Anggota 3)
5. `feature/tests`, `ci/github-actions`, `docs/readme` (Anggota 4)
6. `develop` → `main`, tag `v0.3.0`

---

## 6. Akun seeder

Semua password: `password`

| Peran | Email | Keterangan |
|---|---|---|
| Admin | `admin@vannstore.test` | |
| Penjual | `rina@vannstore.test` | Toko "Rajut Rina" (5 produk) |
| Penjual | `bagas@vannstore.test` | Toko "Kayu Bagas Workshop" (5 produk) |
| Pembeli | `andi@vannstore.test` | |
| Pembeli | `sari@vannstore.test` | |

Produk uji khusus: **Gelang Manik Etnik** stok 1 (uji stok terakhir), **Miniatur Vespa Kayu** stok 0 (uji produk habis).

---

## 7. Menjalankan di lokal

Kebutuhan: PHP ≥ 8.3, Composer, MySQL (atau SQLite untuk coba cepat).

```bash
git clone <url-repo> && cd vannstore-api
composer install
cp .env.example .env
php artisan key:generate
# buat database "vannstore" di MySQL, sesuaikan DB_USERNAME/DB_PASSWORD di .env
php artisan migrate:fresh --seed
php artisan serve
```

Pakai SQLite tanpa MySQL: di `.env` ganti `DB_CONNECTION=sqlite` dan hapus baris `DB_HOST` s/d `DB_PASSWORD`, lalu buat file `database/database.sqlite`.

**Untuk `php artisan test`**, ekstensi SQLite PHP harus aktif. Di Windows buka `php.ini`, hapus tanda `;` pada:
```
extension=pdo_sqlite
extension=sqlite3
```

---

## 8. Tabel kontribusi (diisi untuk PDF)

| Nama | NIM | Peran | Kontribusi | PR |
|---|---|---|---|---|
| [Nama] | [NIM] | Anggota 1 — Basis data & alur pesanan | Setup proyek, ERD → migration/model/seeder, OrderService, RecommendationService, endpoint pesanan & rekomendasi | `feature/data-model`, `feature/order-api` |
| [Nama] | [NIM] | Anggota 2 — Autentikasi & katalog | | |
| [Nama] | [NIM] | Anggota 3 — Policy & error handling | | |
| [Nama] | [NIM] | Anggota 4 — Test, CI, dokumentasi | | |
