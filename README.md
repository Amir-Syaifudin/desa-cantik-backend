# Desa Cantik API
**Sistem Informasi Desa Cinta Statistik Toraja Utara**  
Backend API - Laravel 12 | PHP 8.2 | MySQL 8.0

[![Laravel](https://img.shields.io/badge/Laravel-12.37-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com)

---
## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Tim Pengembang](#tim-pengembang)
- [Tech Stack](#tech-stack)
- [Fitur Utama](#fitur-utama)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Struktur Database](#struktur-database)
- [Test Credentials](#test-credentials)
- [Development Workflow](#development-workflow)
- [API Documentation](#api-documentation)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [Documentation](#documentation)
- [Project Structure](#project-structure)
- [Security](#security)
- [License](#license)
- [Contact & Support](#contact--support)
- [Acknowledgments](#acknowledgments)
- [Project Stats](#project-stats)

---

## Tentang Proyek

**Sistem Informasi Desa Cinta Statistik (Cantik)** merupakan sistem informasi berbasis web untuk pengelolaan dan publikasi data statistik desa binaan BPS Kabupaten Toraja Utara, saat ini meliputi Desa Nonongan Selatan dan Desa Rindingbatu.

### Tujuan Sistem
- **Pengelolaan Data Statistik**: Mengelola indikator statistik desa secara terstruktur
- **Publikasi**: Upload dan kelola dokumen publikasi
- **Peta Tematik**: Visualisasi data geospasial dengan GeoJSON
- **Multi-User Access**: Role-based access (Pegawai BPS, Perangkat Desa, Masyarakat Umum)
- **Keamanan Data**: Menggunakan JWT authentication dan authorization

### Lingkup Proyek
- **Organisasi:** BPS Kabupaten Toraja Utara
- **Desa Binaan:** Nonongan Selatan, Rindingbatu

---

## Tim Pengembang

**Tim 4 Kelas 3SI1**

| Nama                                  | NIM       | Role                               |
| ------------------------------------- | --------- | ---------------------------------- |
| **Teguh Christianto Simbolon**        | 222313403 | Project Manager, Backend Developer |
| **Alif Zakiansyah As Syauqi**         | 222312958 | Lead Backend Developer             |
| **Ahmad Adib Husaini Al Munawwar**    | 222312948 | Backend Developer                  |
| **Amir Syaifudin**                    | 222312968 | Lead Frontend Developer            |
| **Anggita Cristin Meylani**           | 222312982 | Frontend Developer                 |
| **Nyimas Virna Salsa Lestari Risqia** | 222313307 | Frontend Developer                 |

**Institusi:** Politeknik Statistika STIS Program Studi D-IV Komputasi Statistik

---

## Tech Stack

### Backend (API)
- **Framework:** Laravel 12.37.0
- **Language:** PHP 8.2.29
- **Database:** MySQL 8.0
- **Web Server:** Nginx Alpine
- **Authentication:** Laravel Sanctum (Bearer Token)
- **API Documentation:** OpenAPI 3.0 (swagger-php attributes) — generated via `php artisan openapi:generate`
- **Testing:** PHPUnit, Pest

### Infrastructure
- **Containerization:** Docker & Docker Compose
- **Orchestration:** Docker Compose v2
- **Version Control:** Git & GitLab
- **CI/CD:** GitLab CI/CD (Planned)

### Development Tools
- **PHP Memory:** 1GB (untuk handling file besar)
- **Upload Limit:** 200MB (publikasi PDF)
- **Database Packet Size:** 200MB
- **Session Driver:** Database
- **Cache Driver:** File (development) / Redis (production)

---

## Fitur Utama

### 1. Manajemen Pengguna & Autentikasi
- Login dengan Laravel Sanctum (Bearer Token)
- Role-based Access Control (RBAC)
  - Admin BPS: Full access to all villages and admin functions
  - Perangkat Desa: Access only to their assigned village data
  - Guest (Public): Read-only access without authentication
- Manajemen profil pengguna
- Reset password dengan token
- Logout & logout all devices

### 2. Manajemen Data Desa
- CRUD daftar desa
- Profil desa (deskripsi, visi-misi, logo)
- Aktivasi/deaktivasi modul per desa
- Tampilkan/sembunyikan desa dari portal publik

### 3. Statistik Desa
- Kelola indikator statistik (kategori, unit, deskripsi)
- Input data statistik time-series (per tahun)
- Impor CSV bulk data
- Ekspor data ke CSV/Excel
- Visualisasi data (chart, graph)

### 4. Publikasi Laporan
- Upload file PDF (max 200MB)
- Metadata publikasi (judul, deskripsi, kategori)
- Download publikasi
- Soft delete

### 5. Peta Tematik
- Upload GeoJSON batas desa
- Manajemen berbagai layer tematik
- Konfigurasi tampilan peta (warna, opacity)
- Link indikator ke peta tematik

---

## Prerequisites

Pastikan sudah terinstall:

- **Docker** v20.10 atau lebih baru
- **Docker Compose** v2.0 atau lebih baru
- **Git** v2.30 atau lebih baru
- **WSL2** (untuk pengguna Windows)

### Verifikasi Instalasi

```bash
docker --version
# Docker version 20.10.x atau lebih baru

docker-compose --version
# Docker Compose version v2.x.x atau lebih baru

git --version
# git version 2.30.x atau lebih baru
```

---

## Quick Start

### 1. Clone Repository

```bash
git clone https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api.git
cd desa-cantik-api
```

### 2. Setup Environment

```bash
# Copy environment file
cp .env.example .env

# Edit .env jika perlu (opsional)
nano .env
```

### 3. Start Docker Containers

```bash
# Build & start semua services
docker-compose up -d --build

# Tunggu ~30 detik sampai semua container ready
```

### 4. Install Dependencies

```bash
# Masuk ke container app
docker-compose exec app bash

# Install Composer packages
composer install

# Generate application key
php artisan key:generate

# Exit container
exit
```

### 5. Run Migrations & Seeders

```bash
# Run database migrations
docker-compose exec app php artisan migrate

# Seed data dummy (includes test data)
docker-compose exec app php artisan db:seed
```

**Seeder yang tersedia:**
- `RoleSeeder` - Roles (bps_admin, village_officer)
- `VillageSeeder` - Master data desa (Nonongan Selatan, Rindingbatu)
- `StatisticTypeSeeder` - Master indikator statistik (10 jenis)
- `UserSeeder` - User test credentials
- `GeospatialDataSeeder` - Data geospasial (GeoJSON)
- `ThematicMapSeeder` - Peta tematik per desa
- `PublicationSeeder` - Publikasi dokumen (10 records)
- `VillageStatisticSeeder` - Data statistik time-series (60 records)
- `VillageModuleSeeder` - Aktivasi modul per desa (6 records)
- `MapPointSeeder` - Titik peta tematik (20 records)

**Jalankan seeder individual:**
```bash
docker-compose exec app php artisan db:seed --class=PublicationSeeder
docker-compose exec app php artisan db:seed --class=VillageStatisticSeeder
docker-compose exec app php artisan db:seed --class=VillageModuleSeeder
docker-compose exec app php artisan db:seed --class=MapPointSeeder
```

### 6. Verify Installation

```bash
# Test API endpoint
curl -I http://localhost:8000

# Expected: HTTP/1.1 200 OK
```

### 7. Access Application

Buka browser:
```
http://localhost:8000
```

**Sukses!** Jika muncul Laravel welcome page, instalasi berhasil!

### 8. Verify Database Seeding

```bash
# Cek data yang sudah terisi
docker-compose exec mysql mysql -u desa_cantik_user -pDesaCantik2025! desa_cantik_db -e "SELECT 'Roles' as table_name, COUNT(*) as count FROM roles UNION ALL SELECT 'Villages', COUNT(*) FROM villages UNION ALL SELECT 'Users', COUNT(*) FROM users UNION ALL SELECT 'Publications', COUNT(*) FROM publications UNION ALL SELECT 'Village Statistics', COUNT(*) FROM village_statistics;"
```

**Expected output:**
- Roles: 2
- Villages: 2
- Users: 3
- Publications: 10
- Village Statistics: 60
- Village Modules: 6
- Map Points: 20

---

## Struktur Database

### Tabel Aplikasi (15 tabel)

| Tabel                   | Deskripsi                                           | Relasi                                    |
| ----------------------- | --------------------------------------------------- | ----------------------------------------- |
| **roles**               | Role user (Pegawai BPS, Perangkat Desa)            | → users                                   |
| **villages**            | Master data desa                                    | → users, village_profiles, village_modules, dll |
| **users**               | Akun pengguna sistem                                | ← roles, ← villages                       |
| **village_profiles**    | Profil lengkap desa (1:1)                           | ← villages                                |
| **village_modules**     | Aktivasi modul per desa                             | ← villages                                |
| **statistic_types**     | Master indikator statistik                          | → village_statistics                       |
| **village_statistics**  | Data statistik time-series                          | ← villages, ← statistic_types             |
| **publications**        | File publikasi PDF                                  | ← villages                                |
| **geospatial_data**     | GeoJSON boundary desa                               | ← villages                                |
| **thematic_maps**       | Layer peta tematik                                  | ← villages                                |
| **map_points**          | Titik lokasi di peta tematik                        | ← thematic_maps                           |
| **thematic_indicators** | Junction table (maps ↔ indicators)                  | ← thematic_maps, ← statistic_types        |
| **activity_logs**       | Log aktivitas pengguna                               | ← users, ← villages                       |
| **media**               | Media files (Spatie Media Library)                  | -                                         |
| **password_reset_tokens** | Token reset password                              | ← users                                   |

### Tabel Laravel System (8 tabel)

- `cache`, `cache_locks` - Cache storage
- `sessions` - Session database driver
- `jobs`, `job_batches`, `failed_jobs` - Queue system
- `migrations` - Migration tracker
- `personal_access_tokens` - Sanctum tokens

**Total:** 23 tabel

### Data Test yang Tersedia

Setelah menjalankan `php artisan db:seed`, database akan terisi dengan:

- **2 Roles**: bps_admin, village_officer
- **2 Villages**: Nonongan Selatan, Rindingbatu
- **3 Users**: Admin BPS + 2 Perangkat Desa
- **2 Village Profiles**: Profil lengkap per desa
- **10 Statistic Types**: Indikator statistik (Populasi, UMKM, dll)
- **60 Village Statistics**: Data statistik 3 tahun (2022-2024) × 10 jenis × 2 desa
- **6 Geospatial Data**: GeoJSON per desa (Polygon, Point, LineString)
- **6 Thematic Maps**: 3 peta per desa (Kepadatan Penduduk, Fasilitas Pendidikan, Fasilitas Kesehatan)
- **20 Map Points**: Titik lokasi di peta tematik
- **10 Publications**: Dokumen publikasi per desa
- **6 Village Modules**: Modul aktif per desa

### Entity Relationship Diagram (ERD)

Lihat dokumentasi lengkap di:
- **Laporan Progres Milestone 2 Tim 4 Kelas 3SI1:** Halaman 48-49

---

## Test Credentials

**Note:** Guest/public users do not need to login - they have read-only access to public data.

### Admin BPS (Full Access)
```
Email: admin@bps.go.id
Password: password123
Role: bps_admin
Access: Full access to all villages and admin functions
```

### Perangkat Desa - Nonongan Selatan
```
Email: nonongan@desacantik.id
Password: password123
Role: village_officer
Access: Data for Desa Nonongan Selatan only
```

### Perangkat Desa - Rindingbatu
```
Email: rindingbatu@desacantik.id
Password: password123
Role: village_officer
Access: Data for Desa Rindingbatu only
```

**PENTING:** Ganti password default sebelum production deployment!

---

## Development Workflow

### Git Branch Strategy (GitFlow)

```
main (production)
 ↑
 └─ develop (integration)
      ↑
      ├─ feature/authentication
      ├─ feature/desa-management
      ├─ feature/statistics
      ├─ feature/publications
      ├─ feature/maps
      └─ bugfix/issue-xxx
```

### Workflow Steps

1. **Create Feature Branch**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```

2. **Development**
   ```bash
   # Make changes
   git add .
   git commit -m "feat: add your feature"
   ```

3. **Push & Create Merge Request**
   ```bash
   git push origin feature/your-feature-name
   ```

4. **Code Review & Merge**
   - Create Merge Request di GitLab
   - Request review dari team
   - Fix review comments
   - Merge ke `develop`

5. **Delete Feature Branch**
   ```bash
   git branch -d feature/your-feature-name
   git push origin --delete feature/your-feature-name
   ```

### Commit Message Convention

Gunakan [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add user authentication API
fix: resolve database connection timeout
docs: update README installation steps
style: format code with PSR-12
refactor: restructure controller methods
test: add unit tests for User model
chore: update composer dependencies
```

---

## API Documentation

### API Documentation (OpenAPI 3.0)

- Base URL: `${APP_URL}/api` (default: `http://localhost:8000/api`)
- Swagger UI: `http://localhost:8000/api/documentation`
- Spec output (generated by `openapi:generate`):
   - JSON: `storage/api-docs/api-docs.json`
   - YAML: `storage/api-docs/api-docs.yaml`

Generate/rebuild manually:
```bash
# generate JSON spec
php artisan openapi:generate

# generate JSON + YAML
php artisan openapi:generate --yaml
```

Notes:
- Authentication uses Sanctum bearer tokens. Use the "Authorize" button in Swagger UI and paste `Bearer <token>` from `/api/v1/auth/login`.
- Annotation sources live in `app/Docs` and controllers under `app/Http/Controllers/Api`.
- To serve the UI locally, the route `/api/documentation` will render a minimal Swagger UI which fetches the JSON spec from `/api/documentation/json`.
 - API generation config: `config/swagger.php` contains the scan paths and output settings used by the generator command. By default it's set to scan `app/Http/Controllers/Api` and `app/Docs` and writes the generated files to `storage/api-docs`.

Quick checks & fetch examples
```bash
# generate JSON only
php artisan openapi:generate

# generate JSON + YAML
php artisan openapi:generate --yaml

# view JSON spec using CLI
curl -s http://localhost:8000/api/documentation/json | jq .

# download YAML spec
curl -o api-docs.yaml http://localhost:8000/api/documentation/yaml
```

### Postman Collection

Postman collection tersedia di:
```
/docs/postman/Desa-Cantik-API.postman_collection.json
```

**Note:** Collection akan dibuat untuk dokumentasi API endpoints lengkap.

### API Endpoints Summary

**Public Endpoints (No Auth Required):**
- `GET /api/v1/villages` - List desa
- `GET /api/v1/villages/{id}` - Detail desa
- `GET /api/v1/villages/{id}/profile` - Profil desa
- `GET /api/v1/villages/{id}/statistics` - Data statistik
- `GET /api/v1/villages/{id}/publications` - Publikasi
- `GET /api/v1/villages/{id}/geospatial` - Data geospasial
- `GET /api/v1/villages/{id}/thematic-maps` - Peta tematik
- `POST /api/v1/auth/register` - Register user baru
- `POST /api/v1/auth/login` - Login

**Protected Endpoints (Require Auth):**
- `GET /api/v1/auth/user` - User profile
- `PUT /api/v1/auth/profile` - Update profile
- `POST /api/v1/villages/{id}/statistics` - Create statistik
- `POST /api/v1/villages/{id}/publications` - Upload publikasi
- `GET /api/v1/dashboard/admin` - Dashboard admin (BPS Admin only)
- `GET /api/v1/dashboard/village` - Dashboard desa

---

##  Troubleshooting

### Container tidak start

```bash
# Check logs
docker-compose logs app
docker-compose logs mysql
docker-compose logs nginx

# Restart containers
docker-compose restart

# Rebuild jika perlu
docker-compose down
docker-compose up -d --build
```

### Error: "Connection refused"

```bash
# Pastikan container running
docker-compose ps

# Expected: 3 containers dengan status "Up"
```

### Error: "Table not found"

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Atau fresh migrate
docker-compose exec app php artisan migrate:fresh --seed
```

### Error: "Permission denied" (Storage)

```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Port 8000 sudah digunakan

```bash
# Edit docker-compose.yml
# Ganti port nginx:
#   ports:
#     - "8080:80"  # Ganti 8000 ke 8080

# Restart
docker-compose down
docker-compose up -d
```

### Debugging Commands

```bash
# Check container logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php artisan tinker

# Check database
docker-compose exec mysql mysql -u desa_cantik_user -p

# Restart specific service
docker-compose restart app
```

---

## Contributing

### Setup Development Environment

```bash
# 1. Clone & setup
git clone https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api.git
cd desa-cantik-api
cp .env.example .env

# 2. Start Docker
docker-compose up -d

# 3. Install dependencies
docker-compose exec app composer install

# 4. Run migrations
docker-compose exec app php artisan migrate:fresh --seed

# 5. Create your feature branch
git checkout -b feature/your-feature
```

### Code Style

Proyek ini menggunakan **PSR-12** coding standard:

```bash
# Format code
docker-compose exec app ./vendor/bin/pint

# Check style
docker-compose exec app ./vendor/bin/phpcs
```

### Running Tests

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter=UserTest

# With coverage
docker-compose exec app php artisan test --coverage
```

### Pull Request Checklist

- [ ] Code follows PSR-12 standard
- [ ] All tests passing
- [ ] No merge conflicts with develop
- [ ] Migration files included (jika ada perubahan DB)
- [ ] API documentation updated (jika ada endpoint baru)
- [ ] Commit messages follow convention
- [ ] Branch name descriptive (feature/*, bugfix/*)

---

## Documentation

### Project Documents

- [Laporan Milestone 2](docs/Laporan-Progres-Milestone-2_3SI1_Tim-4.pdf)
- **Database ERD**, lihat di Laporan Milestone 2 (halaman 48-49)
- **API Specification**, dalam pengembangan

### Additional Resources

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Docker Documentation](https://docs.docker.com)
- [GitLab CI/CD Guide](https://docs.gitlab.com/ee/ci/)
- [Sanctum Documentation](https://laravel.com/docs/12.x/sanctum)

---

## Project Structure

```
desa-cantik-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # API Controllers
│   │   ├── Middleware/     # Custom middleware
│   │   └── Resources/      # API Resources
│   ├── Models/             # Eloquent Models
│   ├── Services/           # Business Logic
│   └── Policies/           # Authorization
├── database/
│   ├── migrations/         # Database migrations (32 files)
│   ├── seeders/            # Database seeders (10 files)
│   └── factories/          # Model factories
├── docker/
│   ├── nginx/              # Nginx configuration
│   ├── php/                # PHP-FPM configuration
│   └── mysql/              # MySQL configuration
├── routes/
│   ├── api.php             # API routes
│   └── web.php             # Web routes
├── tests/
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
├── docs/                   # Project documentation
├── storage/                # Storage & logs
├── docker-compose.yml      # Docker orchestration
├── .env.example            # Environment template
├── docker-compose.yml       # Docker orchestration
├── composer.json            # PHP dependencies
├── phpunit.xml              # PHPUnit configuration
└── README.md                # This file
```

---

## Security

### Pelaporan Vulnerability

Jika menemukan security issue, **JANGAN** buat public issue. Hubungi:

- **Project Manager:** Teguh Christianto Simbolon (222313403@stis.ac.id)
- **Lead Backend Developer:** Alif Zakiansyah As Syauqi (222312958@stis.ac.id)

### Security Features
- Laravel Sanctum Authentication (Bearer Token)
- Password hashing (bcrypt)
- SQL Injection prevention (Eloquent ORM)
- XSS protection (Laravel built-in)
- CSRF protection
- Rate limiting (throttle middleware)
- CORS configuration
- Soft deletes untuk data sensitif
- Activity logging untuk audit trail  

---

## License

Proyek ini dikembangkan untuk keperluan akademik dengan lisensi sebagai berikut.

**Copyright © 2025 Tim 4 - Kelas 3SI1**  
**Politeknik Statistika STIS**

Untuk keperluan pendidikan dan penelitian. Tidak untuk penggunaan komersial tanpa izin.

---

## Contact & Support

### Support

- **Technical Issues:** Buat GitLab Issue
- **Questions:** Hubungi Project Manager atau Lead Backend Developer
- **Documentation:** Periksa di folder `/docs`

### Links

- **GitLab Repository:** https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api
- **Project Board:** https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api/-/boards
- **Milestones:** https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api/-/milestones

---

## Acknowledgments

Terima kasih kepada:
- **Tim Desa Cantik BPS Kabupaten Toraja Utara** atas segala dukungan dan sumber daya yang diberikan.
- **Dosen Pembimbing** atas segala bimbingan dan petunjuk yang diberikan.
- **Politeknik Statistika STIS**
- **Open Source Community**

---

## Project Stats

![GitHub last commit](https://img.shields.io/badge/last%20commit-November%202025-brightgreen)
![GitHub contributors](https://img.shields.io/badge/contributors-6-blue)
![PHP Version](https://img.shields.io/badge/PHP-8.2-777BB4)
![Laravel Version](https://img.shields.io/badge/Laravel-12.37-FF2D20)
![Database](https://img.shields.io/badge/MySQL-8.0-4479A1)

---

<div align="center">

### Dibangun dengan lancar dan nyaman oleh Tim 4 Kelas 3SI1

**Politeknik Statistika STIS • Jakarta • 2025**

[Documentation](docs/) • [Report Bug](https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api/-/issues) • [Request Feature](https://git.stis.ac.id/rpl-lancarnyaman/desa-cantik-api/-/issues)

</div>
