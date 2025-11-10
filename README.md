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
1. Pastikan Docker atau Podman & Docker Compose/Podman Compose terpasang.
2. Salin berkas lingkungan:
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan variabel lingkungan pada `.env` agar selaras dengan konfigurasi `docker-compose.yml` (terutama `DB_HOST=mysql`, `DB_PORT=3306`, `DB_DATABASE=desa_cantik_db`, `DB_USERNAME=desa_cantik_user`, `DB_PASSWORD=DesaCantik2025!`).
4. Bangun dan jalankan seluruh layanan (PHP-FPM, Nginx, MySQL):
   ```bash
   docker-compose up -d --build
   # atau jika menggunakan Podman:
   # podman compose up -d --build
   ```
   - Service `app` dibangun dari `docker/php/Dockerfile`.
   - Nginx pada service `nginx` memetakan port `8000:80`.
   - MySQL menyimpan data di volume `mysql_data` dan terpapar pada port host (sesuai `docker-compose.yml`, misalnya 3400).
5. Pasang dependensi PHP di dalam container:
   ```bash
   docker-compose exec app composer install
   ```
6. Generate kunci aplikasi Laravel:
   ```bash
   docker-compose exec app php artisan key:generate
   ```
7. Jalankan migrasi basis data:
   ```bash
   docker-compose exec app php artisan migrate
   ```
8. Akses aplikasi di `http://localhost:8000` (akan menampilkan health check JSON).
9. Hentikan layanan bila diperlukan:
   ```bash
   docker-compose down
   ```
