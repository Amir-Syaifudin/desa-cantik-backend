# Desa Cantik API

Layanan web backend murni berbasis Laravel 12. Seluruh aset front-end, Vite, dan Blade telah dihapus sehingga repositori ini hanya berfokus pada API HTTP dan pekerjaan latar belakang.

## Persyaratan
- PHP 8.2+ beserta ekstensi yang dibutuhkan Laravel
- Composer
- Basis data yang didukung (MySQL, PostgreSQL, SQLite, dll.)

## Langkah Awal
1. Pasang dependensi dan siapkan berkas lingkungan:
   ```bash
   composer run setup
   ```
2. Perbarui `.env` dengan kredensial basis data serta konfigurasi queue/cache yang Anda gunakan.
3. Jalankan aplikasi secara lokal:
   ```bash
   php artisan serve
   ```

## Pengujian
```bash
php artisan test
```

## Catatan Proyek
- Hanya rute API (`routes/api.php`) yang seharusnya diekspos; `routes/web.php` kini menampilkan respons JSON sederhana sebagai health check.
- Node.js/Vite tidak lagi diperlukan. Jika membutuhkan antarmuka pengguna, kelola di repositori terpisah.

## Menjalankan dengan Docker
1. Pastikan Docker & Docker Compose terpasang.
2. Sesuaikan variabel lingkungan pada `.env` agar selaras dengan konfigurasi `docker-compose.yml` (terutama host, port, dan kredensial MySQL).
3. Bangun dan jalankan seluruh layanan (PHP-FPM, Nginx, MySQL) menggunakan:
   ```bash
   docker-compose up -d --build
   ```
   - Service `app` dibangun dari `docker/php/Dockerfile`.
   - Nginx pada service `nginx` memetakan port `8000:80`.
   - MySQL menyimpan data di volume `mysql_data`.
4. Hentikan layanan bila diperlukan:
   ```bash
   docker-compose down
   ```
