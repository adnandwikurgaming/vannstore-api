# VannStore API

Backend REST API untuk aplikasi VannStore (E-commerce Produk Kerajinan). Dibangun menggunakan Laravel dan Laravel Sanctum untuk autentikasi. Proyek ini merupakan tugas Sistem Web dan Seluler Lanjut.

## Kebutuhan Sistem
- PHP >= 8.3 (Direkomendasikan 8.4/8.5)
- Composer
- MySQL (atau SQLite untuk pengujian cepat)
- Ekstensi PHP: pdo_sqlite, sqlite3, mbstring, xml, ctype, iconv, bcmath

## Cara Instalasi (Local Development)

1. Clone repositori ini:
   git clone <url-repo-anda>
   cd vannstore-api

2. Install dependensi Composer:
   composer install

3. Salin file environment dan generate key:
   cp .env.example .env
   php artisan key:generate

4. Buat database baru di MySQL dengan nama vannstore (atau sesuaikan dengan .env Anda). 
   Buka file .env dan sesuaikan pengaturan database:
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=vannstore
   DB_USERNAME=root
   DB_PASSWORD=

5. Jalankan migrasi dan seeder:
   php artisan migrate:fresh --seed

6. Buat link storage untuk foto produk:
   php artisan storage:link

7. Jalankan server lokal:
   php artisan serve

   API akan berjalan di http://127.0.0.1:8000/api/v1.

## Akun Seeder (Untuk Pengujian)
Semua akun di bawah ini menggunakan password: password

| Peran | Email | Keterangan |
| :--- | :--- | :--- |
| Admin | admin@vannstore.test | Akses penuh ke panel admin |
| Penjual | rina@vannstore.test | Toko "Rajut Rina" (5 produk) |
| Penjual | bagas@vannstore.test | Toko "Kayu Bagas Workshop" (5 produk) |
| Pembeli | andi@vannstore.test | Pembeli dummy 1 |
| Pembeli | sari@vannstore.test | Pembeli dummy 2 |

Catatan: Produk uji khusus tersedia, yaitu Gelang Manik Etnik (stok 1) dan Miniatur Vespa Kayu (stok 0).

## Menjalankan Pengujian (Testing)
Proyek ini dilengkapi dengan Feature Test dan dikonfigurasi menggunakan GitHub Actions (CI).

Untuk menjalankan test di lokal (menggunakan SQLite):
1. Pastikan ekstensi pdo_sqlite aktif di php.ini Anda.
2. Jalankan perintah:
   php artisan test

## Daftar Endpoint API
Semua endpoint (kecuali register & login) wajib menyertakan header:
Authorization: Bearer <token> dan Accept: application/json.

| Method | Endpoint | Peran | Deskripsi |
| :--- | :--- | :--- | :--- |
| POST | /api/v1/register | Publik | Registrasi akun baru (pembeli/penjual) |
| POST | /api/v1/login | Publik | Login untuk mendapatkan token Sanctum |
| POST | /api/v1/logout | Semua | Menghapus token aktif |
| GET | /api/v1/products | Pembeli | Katalog produk (mendukung filter q & category_id) |
| GET | /api/v1/products/{id} | Pembeli | Detail produk (otomatis mencatat riwayat lihat) |
| GET | /api/v1/categories | Semua | Daftar kategori |
| GET | /api/v1/recommendations | Pembeli | Rekomendasi produk (berdasarkan riwayat lihat/beli) |
| POST | /api/v1/orders | Pembeli | Checkout pesanan (multi-penjual) |
| GET | /api/v1/orders/me | Pembeli | Riwayat pesanan pembeli |
| GET | /api/v1/orders/{id} | Pembeli/Penjual | Detail pesanan |
| GET | /api/v1/seller/orders | Penjual | Daftar pesanan masuk (filter ?status=) |
| PATCH | /api/v1/seller/orders/{id}/status | Penjual | Ubah status pesanan |
| GET | /api/v1/seller/products | Penjual | Daftar produk milik penjual |
| POST | /api/v1/seller/products | Penjual | Tambah produk baru |
| PATCH | /api/v1/seller/products/{id} | Penjual | Edit produk |
| PATCH | /api/v1/seller/products/{id}/stock | Penjual | Update stok produk |
| GET | /api/v1/admin/categories | Admin | Daftar kategori (panel admin) |
| POST | /api/v1/admin/categories | Admin | Tambah kategori baru |
| PATCH | /api/v1/admin/products/{id}/deactivate | Admin | Nonaktifkan produk |

## Format Respons Error
API ini menggunakan format galat JSON yang seragam agar mudah dikonsumsi oleh aplikasi Flutter:

{
  "message": "Pesan error utama",
  "errors": {
    "field": ["Pesan error spesifik"]
  }
}

Kode Error yang digunakan: 401 (Unauthorized), 403 (Forbidden), 404 (Not Found), 409 (Conflict/Stok), 422 (Validation Error), 429 (Too Many Requests).

## Catatan Produksi
Pastikan .env di server produksi diatur menjadi:
APP_DEBUG=false
APP_ENV=production