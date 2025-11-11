# Dokumen Spesifikasi Teknis Backend
## Sistem Informasi Desa Cinta Statistik (Cantik)
### BPS Kabupaten Toraja Utara

**Versi:** 1.0  
**Tanggal:** 10 November 2025  
**Tim Pengembang:** Tim 4 Kelas 3SI1

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Manajemen Hak Akses dan Role](#3-manajemen-hak-akses-dan-role)
4. [Model Data dan Entitas](#4-model-data-dan-entitas)
5. [Skema Database](#5-skema-database)
6. [API Endpoints](#6-api-endpoints)
7. [Keamanan dan Validasi](#7-keamanan-dan-validasi)
8. [Logging dan Audit Trail](#8-logging-dan-audit-trail)
9. [Penanganan Error](#9-penanganan-error)
10. [Pertimbangan Performa](#10-pertimbangan-performa)

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
Dokumen ini menjelaskan spesifikasi teknis backend untuk Sistem Informasi Desa Cinta Statistik (Cantik) yang dikembangkan menggunakan Laravel framework dengan PHP sebagai bahasa pemrograman dan MySQL sebagai database management system.

### 1.2 Ruang Lingkup
Dokumen ini mencakup:
- Struktur database dan relasi antar tabel
- Definisi API endpoints dan fungsinya
- Sistem manajemen hak akses berbasis role
- Mekanisme keamanan dan validasi data
- Logging dan audit trail

### 1.3 Stack Teknologi
- **Backend Framework:** Laravel 10.x
- **Bahasa:** PHP 8.1+
- **Database:** MySQL 8.0+
- **Web Server:** Apache 2.4+
- **Version Control:** GitLab
- **Authentication:** Laravel Sanctum

---

## 2. Arsitektur Sistem

### 2.1 Arsitektur Three-Tier

```
┌─────────────────────────────────────┐
│     Presentation Layer              │
│  (React.js + Tailwind CSS)          │
└──────────────┬──────────────────────┘
               │ HTTPS/REST API
┌──────────────▼──────────────────────┐
│     Application Layer               │
│  (Laravel PHP Framework)            │
│  - Business Logic                   │
│  - API Controllers                  │
│  - Authentication & Authorization   │
│  - Data Validation                  │
└──────────────┬──────────────────────┘
               │ Query/ORM
┌──────────────▼──────────────────────┐
│     Data Layer                      │
│  (MySQL Database)                   │
│  - Relational Data Storage          │
│  - Transaction Management           │
└─────────────────────────────────────┘
```

### 2.2 Pola Desain
- **MVC (Model-View-Controller):** Struktur dasar Laravel
- **Repository Pattern:** Untuk abstraksi akses data
- **Service Layer:** Untuk business logic yang kompleks
- **Middleware:** Untuk authentication, authorization, dan validasi

---

## 3. Manajemen Hak Akses dan Role

### 3.1 Definisi Role

Sistem memiliki 3 role utama:

#### 3.1.1 Role: Guest (Masyarakat Umum)
**Kode Role:** `guest`  
**Deskripsi:** Pengguna yang tidak login, hanya dapat mengakses informasi publik

**Hak Akses:**
- ✅ Melihat daftar desa yang aktif
- ✅ Melihat profil umum desa
- ✅ Melihat data statistik desa
- ✅ Melihat visualisasi data (grafik, peta tematik)
- ✅ Melihat publikasi laporan
- ✅ Mengunduh data dalam format CSV/Excel
- ❌ Tidak dapat mengakses dashboard admin
- ❌ Tidak dapat melakukan perubahan data

#### 3.1.2 Role: Village Officer (Perangkat Desa)
**Kode Role:** `village_officer`  
**Deskripsi:** Perangkat desa yang mengelola data untuk desa yang diampunya

**Hak Akses:**
- ✅ Semua hak akses Guest
- ✅ Login ke sistem
- ✅ Akses dashboard perangkat desa
- ✅ Mengelola data statistik desa sendiri (CRUD)
- ✅ Mengelola profil umum desa sendiri (Update)
- ✅ Mengelola publikasi laporan desa sendiri (Upload, Update, Delete)
- ✅ Mengelola data geospasial desa sendiri
- ✅ Mengelola peta tematik desa sendiri
- ✅ Mengimpor data statistik (CSV/Excel)
- ✅ Mengubah profil dan password sendiri
- ❌ Tidak dapat mengakses data desa lain
- ❌ Tidak dapat mengelola akun pengguna lain
- ❌ Tidak dapat mengelola daftar desa

#### 3.1.3 Role: BPS Admin (Tim Desa Cantik BPS)
**Kode Role:** `bps_admin`  
**Deskripsi:** Administrator dari BPS Toraja Utara dengan akses penuh

**Hak Akses:**
- ✅ Semua hak akses Village Officer
- ✅ Akses dashboard admin BPS
- ✅ Mengelola akun perangkat desa (CRUD)
- ✅ Mengelola daftar desa (CRUD)
- ✅ Mengelola data statistik semua desa (CRUD)
- ✅ Mengelola profil umum semua desa (CRUD)
- ✅ Mengelola publikasi semua desa (CRUD)
- ✅ Mengaktifkan/menonaktifkan modul spesifik desa
- ✅ Mengaktifkan/menonaktifkan desa dari tampilan publik
- ✅ Melihat log aktivitas sistem
- ✅ Mengakses semua data desa

### 3.2 Matriks Hak Akses

| Fitur | Guest | Village Officer | BPS Admin |
|-------|-------|-----------------|-----------|
| **Autentikasi** |
| Login | ❌ | ✅ | ✅ |
| Logout | ❌ | ✅ | ✅ |
| Reset Password | ❌ | ✅ | ✅ |
| Ubah Password | ❌ | ✅ (sendiri) | ✅ (sendiri) |
| **Manajemen Akun** |
| Lihat Daftar Akun | ❌ | ❌ | ✅ |
| Tambah Akun | ❌ | ❌ | ✅ |
| Edit Akun | ❌ | ✅ (sendiri) | ✅ (semua) |
| Hapus Akun | ❌ | ❌ | ✅ |
| **Manajemen Desa** |
| Lihat Daftar Desa (Publik) | ✅ | ✅ | ✅ |
| Tambah Desa | ❌ | ❌ | ✅ |
| Edit Desa | ❌ | ❌ | ✅ |
| Hapus Desa | ❌ | ❌ | ✅ |
| Toggle Status Desa | ❌ | ❌ | ✅ |
| **Profil Desa** |
| Lihat Profil Desa | ✅ | ✅ | ✅ |
| Edit Profil Desa | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| **Data Statistik** |
| Lihat Data Statistik | ✅ | ✅ | ✅ |
| Tambah Data Statistik | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Edit Data Statistik | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Hapus Data Statistik | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Import Data (CSV/Excel) | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Export Data | ✅ | ✅ | ✅ |
| **Publikasi** |
| Lihat Publikasi | ✅ | ✅ | ✅ |
| Upload Publikasi | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Edit Publikasi | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Hapus Publikasi | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| **Data Geospasial** |
| Lihat Peta | ✅ | ✅ | ✅ |
| Kelola Data Geospasial | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| Kelola Peta Tematik | ❌ | ✅ (desa sendiri) | ✅ (semua) |
| **Modul Desa** |
| Toggle Modul | ❌ | ❌ | ✅ |
| **Sistem** |
| Lihat Log Aktivitas | ❌ | ❌ | ✅ |

### 3.3 Implementasi Authorization

#### 3.3.1 Middleware
```php
// app/Http/Middleware/RoleMiddleware.php
class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $userRole = auth()->user()->role;
        
        if (!in_array($userRole, $roles)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        return $next($request);
    }
}
```

#### 3.3.2 Policy untuk Kontrol Akses Granular
```php
// app/Policies/VillageDataPolicy.php
class VillageDataPolicy
{
    /**
     * Determine if user can view village data
     */
    public function view(User $user, Village $village)
    {
        // Guest dapat melihat jika desa aktif
        if ($village->is_active) {
            return true;
        }
        
        // Village officer hanya untuk desa sendiri
        if ($user->role === 'village_officer') {
            return $user->village_id === $village->id;
        }
        
        // BPS admin dapat melihat semua
        return $user->role === 'bps_admin';
    }
    
    /**
     * Determine if user can update village data
     */
    public function update(User $user, Village $village)
    {
        if ($user->role === 'bps_admin') {
            return true;
        }
        
        if ($user->role === 'village_officer') {
            return $user->village_id === $village->id;
        }
        
        return false;
    }
}
```

---

## 4. Model Data dan Entitas

### 4.1 Diagram Entity Relationship (ERD)

```
┌─────────────────┐
│     users       │
├─────────────────┤
│ id (PK)         │
│ username        │
│ email           │
│ password        │
│ role            │
│ village_id (FK) │◄───┐
│ full_name       │    │
│ phone           │    │
│ is_active       │    │
│ created_at      │    │
│ updated_at      │    │
└─────────────────┘    │
                       │
┌─────────────────────┐│
│     villages        ││
├─────────────────────┤│
│ id (PK)            │├┘
│ code               │
│ name               │
│ district           │
│ subdistrict        │
│ is_active          │
│ display_order      │
│ created_at         │
│ updated_at         │
└──────┬──────────────┘
       │
       │ 1:N
       ├───────────────────────────┐
       │                           │
       ▼                           ▼
┌──────────────────┐    ┌──────────────────────┐
│ village_profiles │    │ village_statistics   │
├──────────────────┤    ├──────────────────────┤
│ id (PK)          │    │ id (PK)              │
│ village_id (FK)  │    │ village_id (FK)      │
│ description      │    │ statistic_type_id(FK)│
│ vision           │    │ indicator_name       │
│ mission          │    │ value                │
│ area             │    │ unit                 │
│ population       │    │ year                 │
│ address          │    │ period               │
│ phone            │    │ source               │
│ email            │    │ notes                │
│ website          │    │ created_by (FK)      │
│ logo_url         │    │ updated_by (FK)      │
│ created_at       │    │ created_at           │
│ updated_at       │    │ updated_at           │
└──────────────────┘    └──────────────────────┘
       │                           ▲
       │ 1:N                       │
       │                           │
       ▼                           │
┌──────────────────┐    ┌──────────────────────┐
│ publications     │    │ statistic_types      │
├──────────────────┤    ├──────────────────────┤
│ id (PK)          │    │ id (PK)              │
│ village_id (FK)  │    │ name                 │
│ title            │    │ code                 │
│ description      │    │ category             │
│ file_path        │    │ description          │
│ file_name        │    │ display_order        │
│ file_size        │    │ is_active            │
│ file_type        │    │ created_at           │
│ published_at     │    │ updated_at           │
│ uploaded_by (FK) │    └──────────────────────┘
│ created_at       │
│ updated_at       │
└──────────────────┘
       │
       │
       ▼
┌──────────────────┐
│ geospatial_data  │
├──────────────────┤
│ id (PK)          │
│ village_id (FK)  │
│ name             │
│ type             │
│ geometry         │
│ properties       │
│ created_by (FK)  │
│ updated_by (FK)  │
│ created_at       │
│ updated_at       │
└──────────────────┘
       │
       │
       ▼
┌──────────────────┐
│ thematic_maps    │
├──────────────────┤
│ id (PK)          │
│ village_id (FK)  │
│ theme_name       │
│ description      │
│ icon             │
│ created_at       │
│ updated_at       │
└──────┬───────────┘
       │
       │ 1:N
       ▼
┌──────────────────┐
│ map_points       │
├──────────────────┤
│ id (PK)          │
│ thematic_map(FK) │
│ name             │
│ description      │
│ category         │
│ latitude         │
│ longitude        │
│ image_url        │
│ additional_info  │
│ created_by (FK)  │
│ updated_by (FK)  │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│ village_modules  │
├──────────────────┤
│ id (PK)          │
│ village_id (FK)  │
│ module_name      │
│ is_enabled       │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│ activity_logs    │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ village_id (FK)  │
│ action           │
│ model_type       │
│ model_id         │
│ old_data         │
│ new_data         │
│ ip_address       │
│ user_agent       │
│ created_at       │
└──────────────────┘
```

### 4.2 Deskripsi Entitas

#### 4.2.1 Users
Menyimpan informasi pengguna sistem (BPS Admin dan Village Officer).

**Atribut:**
- `id`: Primary key
- `username`: Username untuk login (unique)
- `email`: Email pengguna (unique)
- `password`: Password yang di-hash
- `role`: Enum ('bps_admin', 'village_officer')
- `village_id`: Foreign key ke villages (nullable untuk bps_admin)
- `full_name`: Nama lengkap pengguna
- `phone`: Nomor telepon
- `is_active`: Status aktif/nonaktif
- `email_verified_at`: Waktu verifikasi email
- `remember_token`: Token untuk remember me
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village (untuk village_officer)
- HasMany: ActivityLogs

#### 4.2.2 Villages
Menyimpan informasi desa-desa yang terdaftar.

**Atribut:**
- `id`: Primary key
- `code`: Kode desa (unique)
- `name`: Nama desa
- `district`: Kecamatan
- `subdistrict`: Kabupaten (default: Toraja Utara)
- `is_active`: Status aktif untuk tampilan publik
- `display_order`: Urutan tampilan
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- HasOne: VillageProfile
- HasMany: Users (village officers)
- HasMany: VillageStatistics
- HasMany: Publications
- HasMany: GeospatialData
- HasMany: ThematicMaps
- HasMany: VillageModules
- HasMany: ActivityLogs

#### 4.2.3 Village Profiles
Menyimpan profil umum desa.

**Atribut:**
- `id`: Primary key
- `village_id`: Foreign key ke villages (unique)
- `description`: Deskripsi singkat desa
- `vision`: Visi desa
- `mission`: Misi desa (JSON array)
- `area`: Luas wilayah (km²)
- `population`: Jumlah penduduk
- `population_density`: Kepadatan penduduk
- `address`: Alamat kantor desa
- `phone`: Telepon kantor desa
- `email`: Email desa
- `website`: Website desa (jika ada)
- `logo_url`: URL logo desa
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village

#### 4.2.4 Statistic Types
Menyimpan jenis/kategori statistik yang tersedia.

**Atribut:**
- `id`: Primary key
- `name`: Nama jenis statistik
- `code`: Kode jenis statistik (unique)
- `category`: Kategori (kependudukan, ekonomi, sosial, dll)
- `description`: Deskripsi
- `display_order`: Urutan tampilan
- `is_active`: Status aktif
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- HasMany: VillageStatistics

#### 4.2.5 Village Statistics
Menyimpan data statistik desa.

**Atribut:**
- `id`: Primary key
- `village_id`: Foreign key ke villages
- `statistic_type_id`: Foreign key ke statistic_types
- `indicator_name`: Nama indikator statistik
- `value`: Nilai statistik
- `unit`: Satuan (jiwa, persen, unit, dll)
- `year`: Tahun data
- `period`: Periode (tahunan, bulanan, dll)
- `source`: Sumber data
- `notes`: Catatan tambahan
- `created_by`: Foreign key ke users
- `updated_by`: Foreign key ke users
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village
- BelongsTo: StatisticType
- BelongsTo: User (creator)
- BelongsTo: User (updater)

#### 4.2.6 Publications
Menyimpan publikasi laporan desa.

**Atribut:**
- `id`: Primary key
- `village_id`: Foreign key ke villages
- `title`: Judul publikasi
- `description`: Deskripsi singkat
- `file_path`: Path file di storage
- `file_name`: Nama file asli
- `file_size`: Ukuran file (bytes)
- `file_type`: Tipe file (pdf, doc, dll)
- `published_at`: Tanggal publikasi
- `uploaded_by`: Foreign key ke users
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village
- BelongsTo: User (uploader)

#### 4.2.7 Geospatial Data
Menyimpan data geospasial desa (batas wilayah, dusun, dll).

**Atribut:**
- `id`: Primary key
- `village_id`: Foreign key ke villages
- `name`: Nama area
- `type`: Tipe (boundary, hamlet, etc)
- `geometry`: Data geometri (GeoJSON)
- `properties`: Properties tambahan (JSON)
- `created_by`: Foreign key ke users
- `updated_by`: Foreign key ke users
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village
- BelongsTo: User (creator)
- BelongsTo: User (updater)

#### 4.2.8 Thematic Maps
Menyimpan tema peta tematik.

**Atribut:**
- `id`: Primary key
- `village_id`: Foreign key ke villages
- `theme_name`: Nama tema (UMKM, Pariwisata, dll)
- `description`: Deskripsi tema
- `icon`: Icon tema
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village
- HasMany: MapPoints

#### 4.2.9 Map Points
Menyimpan titik-titik lokasi pada peta tematik.

**Atribut:**
- `id`: Primary key
- `thematic_map_id`: Foreign key ke thematic_maps
- `name`: Nama lokasi
- `description`: Deskripsi
- `category`: Kategori
- `latitude`: Koordinat latitude
- `longitude`: Koordinat longitude
- `image_url`: URL gambar lokasi
- `additional_info`: Informasi tambahan (JSON)
- `created_by`: Foreign key ke users
- `updated_by`: Foreign key ke users
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: ThematicMap
- BelongsTo: User (creator)
- BelongsTo: User (updater)

#### 4.2.10 Village Modules
Menyimpan status modul untuk setiap desa.

**Atribut:**
- `id`: Primary key
- `village_id`: Foreign key ke villages
- `module_name`: Nama modul (statistics, publications, thematic_maps, dll)
- `is_enabled`: Status aktif/nonaktif
- `created_at`, `updated_at`: Timestamp

**Relasi:**
- BelongsTo: Village

#### 4.2.11 Activity Logs
Menyimpan log aktivitas pengguna (audit trail).

**Atribut:**
- `id`: Primary key
- `user_id`: Foreign key ke users
- `village_id`: Foreign key ke villages (nullable)
- `action`: Aksi yang dilakukan (create, update, delete)
- `model_type`: Tipe model yang diubah
- `model_id`: ID model yang diubah
- `old_data`: Data lama (JSON)
- `new_data`: Data baru (JSON)
- `ip_address`: IP address pengguna
- `user_agent`: User agent browser
- `created_at`: Timestamp

**Relasi:**
- BelongsTo: User
- BelongsTo: Village

---

## 5. Skema Database

### 5.1 SQL Schema

```sql
-- Table: users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('bps_admin', 'village_officer') NOT NULL,
    village_id BIGINT UNSIGNED NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role (role),
    INDEX idx_village_id (village_id),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: villages
CREATE TABLE villages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    district VARCHAR(255) NOT NULL,
    subdistrict VARCHAR(255) DEFAULT 'Toraja Utara',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order),
    FULLTEXT idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: village_profiles
CREATE TABLE village_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED UNIQUE NOT NULL,
    description TEXT,
    vision TEXT,
    mission JSON,
    area DECIMAL(10, 2) COMMENT 'Luas wilayah dalam km²',
    population INT UNSIGNED,
    population_density DECIMAL(10, 2) COMMENT 'Jiwa per km²',
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: statistic_types
CREATE TABLE statistic_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: village_statistics
CREATE TABLE village_statistics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED NOT NULL,
    statistic_type_id BIGINT UNSIGNED NOT NULL,
    indicator_name VARCHAR(255) NOT NULL,
    value DECIMAL(20, 4) NOT NULL,
    unit VARCHAR(50),
    year YEAR NOT NULL,
    period VARCHAR(50) COMMENT 'Tahunan, Semester I, Semester II, dll',
    source VARCHAR(255),
    notes TEXT,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_village_id (village_id),
    INDEX idx_statistic_type_id (statistic_type_id),
    INDEX idx_year (year),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    FOREIGN KEY (statistic_type_id) REFERENCES statistic_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: publications
CREATE TABLE publications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL COMMENT 'Size in bytes',
    file_type VARCHAR(10) NOT NULL,
    published_at DATE NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```sql
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_village_id (village_id),
    INDEX idx_published_at (published_at),
    INDEX idx_uploaded_by (uploaded_by),
    FULLTEXT idx_title_description (title, description),
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: geospatial_data
CREATE TABLE geospatial_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'boundary, hamlet, subarea, etc',
    geometry JSON NOT NULL COMMENT 'GeoJSON format',
    properties JSON COMMENT 'Additional properties',
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_village_id (village_id),
    INDEX idx_type (type),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: thematic_maps
CREATE TABLE thematic_maps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED NOT NULL,
    theme_name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_village_id (village_id),
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: map_points
CREATE TABLE map_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thematic_map_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    image_url VARCHAR(500),
    additional_info JSON,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_thematic_map_id (thematic_map_id),
    INDEX idx_category (category),
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (thematic_map_id) REFERENCES thematic_maps(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: village_modules
CREATE TABLE village_modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id BIGINT UNSIGNED NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_village_id (village_id),
    INDEX idx_module_name (module_name),
    UNIQUE KEY unique_village_module (village_id, module_name),
    
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: activity_logs
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    village_id BIGINT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    model_type VARCHAR(100) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_village_id (village_id),
    INDEX idx_action (action),
    INDEX idx_model (model_type, model_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: password_reset_tokens
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: personal_access_tokens (Laravel Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5.2 Data Seeding

```sql
-- Insert default statistic types
INSERT INTO statistic_types (name, code, category, description, display_order) VALUES
('Jumlah Penduduk', 'POPULATION_TOTAL', 'kependudukan', 'Total jumlah penduduk desa', 1),
('Jumlah Penduduk Laki-laki', 'POPULATION_MALE', 'kependudukan', 'Jumlah penduduk laki-laki', 2),
('Jumlah Penduduk Perempuan', 'POPULATION_FEMALE', 'kependudukan', 'Jumlah penduduk perempuan', 3),
('Jumlah Kepala Keluarga', 'HOUSEHOLDS', 'kependudukan', 'Jumlah kepala keluarga', 4),
('Angka Melek Huruf', 'LITERACY_RATE', 'pendidikan', 'Persentase penduduk yang dapat membaca dan menulis', 5),
('Jumlah UMKM', 'UMKM_TOTAL', 'ekonomi', 'Total UMKM yang terdaftar', 6),
('Tingkat Kemiskinan', 'POVERTY_RATE', 'kesejahteraan', 'Persentase penduduk miskin', 7),
('Luas Lahan Pertanian', 'AGRICULTURAL_LAND', 'ekonomi', 'Luas lahan pertanian dalam hektar', 8),
('Jumlah Fasilitas Kesehatan', 'HEALTH_FACILITIES', 'kesehatan', 'Total fasilitas kesehatan (Puskesmas, Posyandu, dll)', 9),
('Jumlah Fasilitas Pendidikan', 'EDUCATION_FACILITIES', 'pendidikan', 'Total fasilitas pendidikan (SD, SMP, SMA, dll)', 10);

-- Insert default BPS Admin
INSERT INTO users (username, email, password, role, full_name, phone, is_active, email_verified_at) VALUES
('admin_bps', 'admin@bpstorut.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bps_admin', 'Administrator BPS Toraja Utara', '081234567890', TRUE, NOW());
-- Password default: password (harus diganti saat production)
```

---

## 6. API Endpoints

### 6.1 Konvensi API

**Base URL:** `https://api.desacantik-torut.go.id/api/v1`

**Format Response:**
```json
{
    "success": true/false,
    "message": "Pesan response",
    "data": {}, // atau [],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

**HTTP Status Codes:**
- `200 OK`: Request berhasil
- `201 Created`: Resource berhasil dibuat
- `204 No Content`: Request berhasil tanpa return data
- `400 Bad Request`: Request tidak valid
- `401 Unauthorized`: Autentikasi gagal
- `403 Forbidden`: Tidak memiliki akses
- `404 Not Found`: Resource tidak ditemukan
- `422 Unprocessable Entity`: Validasi gagal
- `500 Internal Server Error`: Error server

### 6.2 Authentication Endpoints

#### 6.2.1 Login
```
POST /auth/login
```

**Request Body:**
```json
{
    "username": "admin_bps",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "username": "admin_bps",
            "email": "admin@bpstorut.go.id",
            "full_name": "Administrator BPS Toraja Utara",
            "role": "bps_admin",
            "village": null
        },
        "token": "1|laravel_sanctum_token_here",
        "token_type": "Bearer"
    }
}
```

**Validasi:**
- `username`: required, string, max:100
- `password`: required, string, min:6

#### 6.2.2 Logout
```
POST /auth/logout
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

#### 6.2.3 Get Current User
```
GET /auth/user
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "admin_bps",
        "email": "admin@bpstorut.go.id",
        "full_name": "Administrator BPS Toraja Utara",
        "role": "bps_admin",
        "village": null,
        "phone": "081234567890",
        "is_active": true
    }
}
```

#### 6.2.4 Request Password Reset
```
POST /auth/password/forgot
```

**Request Body:**
```json
{
    "email": "perangkat@desa.go.id"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Link reset password telah dikirim ke email Anda"
}
```

**Validasi:**
- `email`: required, email, exists:users,email

#### 6.2.5 Reset Password
```
POST /auth/password/reset
```

**Request Body:**
```json
{
    "email": "perangkat@desa.go.id",
    "token": "reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password berhasil diubah"
}
```

**Validasi:**
- `email`: required, email, exists:users,email
- `token`: required, string
- `password`: required, string, min:8, confirmed

#### 6.2.6 Change Password
```
PUT /auth/password/change
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password berhasil diubah"
}
```

**Validasi:**
- `current_password`: required, string
- `new_password`: required, string, min:8, confirmed
- `new_password_confirmation`: required, string

---

### 6.3 User Management Endpoints (BPS Admin Only)

#### 6.3.1 Get All Users
```
GET /users?page=1&per_page=15&role=village_officer&village_id=1&search=nama
Authorization: Bearer {token}
Role: bps_admin
```

**Query Parameters:**
- `page`: integer, default: 1
- `per_page`: integer, default: 15, max: 100
- `role`: string, filter by role
- `village_id`: integer, filter by village
- `search`: string, search by full_name, username, or email

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "username": "perangkat_desa1",
            "email": "perangkat1@desa.go.id",
            "full_name": "Perangkat Desa 1",
            "role": "village_officer",
            "village": {
                "id": 1,
                "name": "Desa Makale",
                "code": "7301012001"
            },
            "phone": "081234567891",
            "is_active": true,
            "created_at": "2025-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 25,
        "last_page": 2
    }
}
```

#### 6.3.2 Get User Detail
```
GET /users/{id}
Authorization: Bearer {token}
Role: bps_admin
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "username": "perangkat_desa1",
        "email": "perangkat1@desa.go.id",
        "full_name": "Perangkat Desa 1",
        "role": "village_officer",
        "village": {
            "id": 1,
            "name": "Desa Makale",
            "code": "7301012001",
            "district": "Makale"
        },
        "phone": "081234567891",
        "is_active": true,
        "email_verified_at": "2025-01-01T00:00:00.000000Z",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
    }
}
```

#### 6.3.3 Create User
```
POST /users
Authorization: Bearer {token}
Role: bps_admin
```

**Request Body:**
```json
{
    "username": "perangkat_desa2",
    "email": "perangkat2@desa.go.id",
    "password": "password123",
    "password_confirmation": "password123",
    "full_name": "Perangkat Desa 2",
    "role": "village_officer",
    "village_id": 2,
    "phone": "081234567892"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Akun pengguna berhasil dibuat",
    "data": {
        "id": 3,
        "username": "perangkat_desa2",
        "email": "perangkat2@desa.go.id",
        "full_name": "Perangkat Desa 2",
        "role": "village_officer",
        "village_id": 2,
        "phone": "081234567892",
        "is_active": true
    }
}
```

**Validasi:**
- `username`: required, string, max:100, unique:users
- `email`: required, email, max:255, unique:users
- `password`: required, string, min:8, confirmed
- `full_name`: required, string, max:255
- `role`: required, in:bps_admin,village_officer
- `village_id`: required_if:role,village_officer, exists:villages,id
- `phone`: nullable, string, max:20

#### 6.3.4 Update User
```
PUT /users/{id}
Authorization: Bearer {token}
Role: bps_admin
```

**Request Body:**
```json
{
    "username": "perangkat_desa2_updated",
    "email": "perangkat2_updated@desa.go.id",
    "full_name": "Perangkat Desa 2 Updated",
    "village_id": 2,
    "phone": "081234567892",
    "is_active": true
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Data pengguna berhasil diperbarui",
    "data": {
        "id": 3,
        "username": "perangkat_desa2_updated",
        "email": "perangkat2_updated@desa.go.id",
        "full_name": "Perangkat Desa 2 Updated",
        "role": "village_officer",
        "village_id": 2,
        "phone": "081234567892",
        "is_active": true
    }
}
```

**Validasi:**
- `username`: sometimes, string, max:100, unique:users,username,{id}
- `email`: sometimes, email, max:255, unique:users,email,{id}
- `full_name`: sometimes, string, max:255
- `village_id`: sometimes, exists:villages,id
- `phone`: nullable, string, max:20
- `is_active`: sometimes, boolean

#### 6.3.5 Delete User
```
DELETE /users/{id}
Authorization: Bearer {token}
Role: bps_admin
```

**Response (200):**
```json
{
    "success": true,
    "message": "Akun pengguna berhasil dihapus"
}
```

**Business Rules:**
- Tidak dapat menghapus akun sendiri
- Tidak dapat menghapus akun yang sedang login

---

### 6.4 Profile Management Endpoints

#### 6.4.1 Get My Profile
```
GET /profile
Authorization: Bearer {token}
Role: bps_admin, village_officer
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "username": "perangkat_desa1",
        "email": "perangkat1@desa.go.id",
        "full_name": "Perangkat Desa 1",
        "role": "village_officer",
        "village": {
            "id": 1,
            "name": "Desa Makale",
            "code": "7301012001"
        },
        "phone": "081234567891",
        "is_active": true
    }
}
```

#### 6.4.2 Update My Profile
```
PUT /profile
Authorization: Bearer {token}
Role: bps_admin, village_officer
```

**Request Body:**
```json
{
    "full_name": "Perangkat Desa 1 Updated",
    "email": "perangkat1_new@desa.go.id",
    "phone": "081234567891"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Profil berhasil diperbarui",
    "data": {
        "id": 2,
        "username": "perangkat_desa1",
        "email": "perangkat1_new@desa.go.id",
        "full_name": "Perangkat Desa 1 Updated",
        "phone": "081234567891"
    }
}
```

**Validasi:**
- `full_name`: sometimes, string, max:255
- `email`: sometimes, email, max:255, unique:users,email,{id}
- `phone`: nullable, string, max:20

---

### 6.5 Village Management Endpoints

#### 6.5.1 Get All Villages (Public)
```
GET /villages?page=1&per_page=15&search=makale&is_active=true
```

**Query Parameters:**
- `page`: integer, default: 1
- `per_page`: integer, default: 15, max: 100
- `search`: string, search by name, district
- `is_active`: boolean, filter active villages (default: true for public)

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "7301012001",
            "name": "Desa Makale",
            "district": "Makale",
            "subdistrict": "Toraja Utara",
            "is_active": true,
            "display_order": 1
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 10,
        "last_page": 1
    }
}
```

#### 6.5.2 Get Village Detail (Public)
```
GET /villages/{id}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "code": "7301012001",
        "name": "Desa Makale",
        "district": "Makale",
        "subdistrict": "Toraja Utara",
        "is_active": true,
        "profile": {
            "description": "Deskripsi desa...",
            "vision": "Visi desa...",
            "mission": ["Misi 1", "Misi 2"],
            "area": 25.50,
            "population": 5000,
            "population_density": 196.08,
            "address": "Jl. Desa No. 1",
            "phone": "0431-123456",
            "email": "desam akale@example.com",
            "website": "https://desamakale.go.id",
            "logo_url": "https://storage.example.com/logos/desa1.png"
        },
        "modules": [
            {
                "module_name": "statistics",
                "is_enabled": true
            },
            {
                "module_name": "publications",
                "is_enabled": true
            },
            {
                "module_name": "thematic_maps",
                "is_enabled": true
            }
        ]
    }
}
```

#### 6.5.3 Create Village (BPS Admin Only)
```
POST /villages
Authorization: Bearer {token}
Role: bps_admin
```

**Request Body:**
```json
{
    "code": "7301012002",
    "name": "Desa Rantepao",
    "district": "Rantepao",
    "subdistrict": "Toraja Utara",
    "display_order": 2
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Desa berhasil ditambahkan",
    "data": {
        "id": 2,
        "code": "7301012002",
        "name": "Desa Rantepao",
        "district": "Rantepao",
        "subdistrict": "Toraja Utara",
        "is_active": true,
        "display_order": 2
    }
}
```

**Validasi:**
- `code`: required, string, max:20, unique:villages
- `name`: required, string, max:255
- `district`: required, string, max:255
- `subdistrict`: sometimes, string, max:255, default: 'Toraja Utara'
- `display_order`: sometimes, integer, default: 0

#### 6.5.4 Update Village (BPS Admin Only)
```
PUT /villages/{id}
Authorization: Bearer {token}
Role: bps_admin
```

**Request Body:**
```json
{
    "code": "7301012002",
    "name": "Desa Rantepao Updated",
    "district": "Rantepao",
    "subdistrict": "Toraja Utara",
    "display_order": 2
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Data desa berhasil diperbarui",
    "data": {
        "id": 2,
        "code": "7301012002",
        "name": "Desa Rantepao Updated",
        "district": "Rantepao",
        "subdistrict": "Toraja Utara",
        "is_active": true,
        "display_order": 2
    }
}
```

**Validasi:**
- `code`: sometimes, string, max:20, unique:villages,code,{id}
- `name`: sometimes, string, max:255
- `district`: sometimes, string, max:255
- `subdistrict`: sometimes, string, max:255
- `display_order`: sometimes, integer

#### 6.5.5 Delete Village (BPS Admin Only)
```
DELETE /villages/{id}
Authorization: Bearer {token}
Role: bps_admin
```

**Response (200):**
```json
{
    "success": true,
    "message": "Desa berhasil dihapus"
}
```

**Business Rules:**
- Tidak dapat menghapus desa yang masih memiliki user terhubung
- Cascade delete untuk profile, statistics, publications, dll

#### 6.5.6 Toggle Village Status (BPS Admin Only)
```
PUT /villages/{id}/toggle-status
Authorization: Bearer {token}
Role: bps_admin
```

**Request Body:**
```json
{
    "is_active": false
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Status desa berhasil diubah",
    "data": {
        "id": 2,
        "is_active": false
    }
}
```

**Validasi:**
- `is_active`: required, boolean

---

### 6.6 Village Profile Endpoints

#### 6.6.1 Get Village Profile (Public)
```
GET /villages/{village_id}/profile
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "village_id": 1,
        "description": "Desa Makale adalah...",
        "vision": "Menjadi desa yang...",
        "mission": [
            "Meningkatkan kesejahteraan masyarakat",
            "Mengembangkan infrastruktur desa"
        ],
        "area": 25.50,
        "population": 5000,
        "population_density": 196.08,
        "address": "Jl. Desa No. 1",
        "phone": "0431-123456",
        "email": "desamakale@example.com",
        "website": "https://desamakale.go.id",
        "logo_url": "https://storage.example.com/logos/desa1.png"
    }
}
```

#### 6.6.2 Update Village Profile
```
PUT /villages/{village_id}/profile
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "description": "Deskripsi desa updated...",
    "vision": "Visi desa updated...",
    "mission": ["Misi 1 updated", "Misi 2 updated", "Misi 3 new"],
    "area": 25.75,
    "population": 5100,
    "address": "Jl. Desa No. 1 Updated",
    "phone": "0431-123457",
    "email": "desamakale_new@example.com",
    "website": "https://desamakale.go.id",
    "logo_url": "https://storage.example.com/logos/desa1_new.png"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Profil desa berhasil diperbarui",
    "data": {
        "id": 1,
        "village_id": 1,
        "description": "Deskripsi desa updated...",
        "vision": "Visi desa updated...",
        "mission": ["Misi 1 updated", "Misi 2 updated", "Misi 3 new"],
        "area": 25.75,
        "population": 5100,
        "population_density": 198.06,
        "address": "Jl. Desa No. 1 Updated",
        "phone": "0431-123457",
        "email": "desamakale_new@example.com",
        "website": "https://desamakale.go.id",
        "logo_url": "https://storage.example.com/logos/desa1_new.png"
    }
}
```

**Validasi:**
- `description`: sometimes, string
- `vision`: sometimes, string
- `mission`: sometimes, array
- `area`: sometimes, numeric, min:0
- `population`: sometimes, integer, min:0
- `address`: sometimes, string
- `phone`: nullable, string, max:20
- `email`: nullable, email
- `website`: nullable, url
- `logo_url`: nullable, url

#### 6.6.3 Upload Village Logo
```
POST /villages/{village_id}/profile/logo
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
logo: (file)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Logo desa berhasil diunggah",
    "
```json
    "data": {
        "logo_url": "https://storage.example.com/logos/village_1_logo_20251110.png"
    }
}
```

**Validasi:**
- `logo`: required, file, mimes:jpeg,jpg,png, max:2048 (2MB)

---

### 6.7 Statistics Management Endpoints

#### 6.7.1 Get Statistic Types (Public)
```
GET /statistic-types?category=kependudukan
```

**Query Parameters:**
- `category`: string, filter by category

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Jumlah Penduduk",
            "code": "POPULATION_TOTAL",
            "category": "kependudukan",
            "description": "Total jumlah penduduk desa",
            "display_order": 1
        },
        {
            "id": 2,
            "name": "Jumlah Penduduk Laki-laki",
            "code": "POPULATION_MALE",
            "category": "kependudukan",
            "description": "Jumlah penduduk laki-laki",
            "display_order": 2
        }
    ]
}
```

#### 6.7.2 Get Village Statistics (Public)
```
GET /villages/{village_id}/statistics?year=2024&statistic_type_id=1&page=1&per_page=15
```

**Query Parameters:**
- `year`: integer, filter by year
- `statistic_type_id`: integer, filter by statistic type
- `page`: integer, default: 1
- `per_page`: integer, default: 15, max: 100

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "village_id": 1,
            "statistic_type": {
                "id": 1,
                "name": "Jumlah Penduduk",
                "code": "POPULATION_TOTAL",
                "category": "kependudukan"
            },
            "indicator_name": "Total Penduduk",
            "value": 5000,
            "unit": "jiwa",
            "year": 2024,
            "period": "Tahunan",
            "source": "Data Kependudukan Desa",
            "notes": "Data per 31 Desember 2024",
            "created_by": {
                "id": 2,
                "full_name": "Perangkat Desa 1"
            },
            "created_at": "2024-12-31T00:00:00.000000Z",
            "updated_at": "2024-12-31T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 50,
        "last_page": 4
    }
}
```

#### 6.7.3 Get Statistics Summary (Public)
```
GET /villages/{village_id}/statistics/summary?year=2024
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "year": 2024,
        "categories": {
            "kependudukan": {
                "total_indicators": 5,
                "statistics": [
                    {
                        "indicator_name": "Total Penduduk",
                        "value": 5000,
                        "unit": "jiwa"
                    },
                    {
                        "indicator_name": "Penduduk Laki-laki",
                        "value": 2500,
                        "unit": "jiwa"
                    }
                ]
            },
            "ekonomi": {
                "total_indicators": 3,
                "statistics": [
                    {
                        "indicator_name": "Jumlah UMKM",
                        "value": 50,
                        "unit": "unit"
                    }
                ]
            }
        }
    }
}
```

#### 6.7.4 Create Village Statistic
```
POST /villages/{village_id}/statistics
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "statistic_type_id": 1,
    "indicator_name": "Total Penduduk",
    "value": 5000,
    "unit": "jiwa",
    "year": 2024,
    "period": "Tahunan",
    "source": "Data Kependudukan Desa",
    "notes": "Data per 31 Desember 2024"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Data statistik berhasil ditambahkan",
    "data": {
        "id": 1,
        "village_id": 1,
        "statistic_type_id": 1,
        "indicator_name": "Total Penduduk",
        "value": 5000,
        "unit": "jiwa",
        "year": 2024,
        "period": "Tahunan",
        "source": "Data Kependudukan Desa",
        "notes": "Data per 31 Desember 2024",
        "created_by": 2,
        "created_at": "2024-12-31T00:00:00.000000Z"
    }
}
```

**Validasi:**
- `statistic_type_id`: required, exists:statistic_types,id
- `indicator_name`: required, string, max:255
- `value`: required, numeric
- `unit`: nullable, string, max:50
- `year`: required, integer, min:2000, max:current_year+1
- `period`: nullable, string, max:50
- `source`: nullable, string, max:255
- `notes`: nullable, string

#### 6.7.5 Update Village Statistic
```
PUT /villages/{village_id}/statistics/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "indicator_name": "Total Penduduk Updated",
    "value": 5100,
    "unit": "jiwa",
    "year": 2024,
    "period": "Tahunan",
    "source": "Data Kependudukan Desa Updated",
    "notes": "Data per 31 Desember 2024 (Updated)"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Data statistik berhasil diperbarui",
    "data": {
        "id": 1,
        "village_id": 1,
        "statistic_type_id": 1,
        "indicator_name": "Total Penduduk Updated",
        "value": 5100,
        "unit": "jiwa",
        "year": 2024,
        "period": "Tahunan",
        "source": "Data Kependudukan Desa Updated",
        "notes": "Data per 31 Desember 2024 (Updated)",
        "updated_by": 2,
        "updated_at": "2025-01-15T10:30:00.000000Z"
    }
}
```

**Validasi:** (sama seperti create, tapi semua field optional dengan `sometimes`)

#### 6.7.6 Delete Village Statistic
```
DELETE /villages/{village_id}/statistics/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Data statistik berhasil dihapus"
}
```

#### 6.7.7 Import Statistics (CSV/Excel)
```
POST /villages/{village_id}/statistics/import
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
file: (file)
```

**Expected CSV/Excel Format:**
```csv
statistic_type_code,indicator_name,value,unit,year,period,source,notes
POPULATION_TOTAL,Total Penduduk,5000,jiwa,2024,Tahunan,Data Desa,Catatan
POPULATION_MALE,Penduduk Laki-laki,2500,jiwa,2024,Tahunan,Data Desa,Catatan
```

**Response (200):**
```json
{
    "success": true,
    "message": "Data statistik berhasil diimpor",
    "data": {
        "total_rows": 10,
        "imported": 8,
        "failed": 2,
        "errors": [
            {
                "row": 3,
                "error": "Statistic type code not found: INVALID_CODE"
            },
            {
                "row": 7,
                "error": "Value must be numeric"
            }
        ]
    }
}
```

**Validasi:**
- `file`: required, file, mimes:csv,xlsx,xls, max:5120 (5MB)

#### 6.7.8 Export Statistics
```
GET /villages/{village_id}/statistics/export?format=csv&year=2024
Authorization: Bearer {token} (Optional for public access)
```

**Query Parameters:**
- `format`: string, required, in:csv,xlsx
- `year`: integer, optional

**Response:** File download (CSV or Excel)

**Headers:**
```
Content-Type: text/csv atau application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="statistik_desa_makale_2024.csv"
```

---

### 6.8 Publications Management Endpoints

#### 6.8.1 Get Village Publications (Public)
```
GET /villages/{village_id}/publications?page=1&per_page=15&year=2024
```

**Query Parameters:**
- `page`: integer, default: 1
- `per_page`: integer, default: 15, max: 100
- `year`: integer, filter by publication year

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "village_id": 1,
            "title": "Laporan Tahunan Desa 2024",
            "description": "Laporan lengkap kegiatan desa tahun 2024",
            "file_name": "laporan_tahunan_2024.pdf",
            "file_size": 2048576,
            "file_type": "pdf",
            "published_at": "2024-12-31",
            "uploaded_by": {
                "id": 2,
                "full_name": "Perangkat Desa 1"
            },
            "download_url": "https://api.example.com/api/v1/publications/1/download",
            "created_at": "2024-12-31T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 5,
        "last_page": 1
    }
}
```

#### 6.8.2 Get Publication Detail (Public)
```
GET /publications/{id}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "village": {
            "id": 1,
            "name": "Desa Makale",
            "code": "7301012001"
        },
        "title": "Laporan Tahunan Desa 2024",
        "description": "Laporan lengkap kegiatan desa tahun 2024",
        "file_name": "laporan_tahunan_2024.pdf",
        "file_size": 2048576,
        "file_type": "pdf",
        "published_at": "2024-12-31",
        "uploaded_by": {
            "id": 2,
            "full_name": "Perangkat Desa 1"
        },
        "download_url": "https://api.example.com/api/v1/publications/1/download",
        "created_at": "2024-12-31T00:00:00.000000Z",
        "updated_at": "2024-12-31T00:00:00.000000Z"
    }
}
```

#### 6.8.3 Upload Publication
```
POST /villages/{village_id}/publications
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
title: Laporan Tahunan Desa 2024
description: Laporan lengkap kegiatan desa tahun 2024
published_at: 2024-12-31
file: (file)
```

**Response (201):**
```json
{
    "success": true,
    "message": "Publikasi berhasil diunggah",
    "data": {
        "id": 1,
        "village_id": 1,
        "title": "Laporan Tahunan Desa 2024",
        "description": "Laporan lengkap kegiatan desa tahun 2024",
        "file_name": "laporan_tahunan_2024.pdf",
        "file_size": 2048576,
        "file_type": "pdf",
        "published_at": "2024-12-31",
        "download_url": "https://api.example.com/api/v1/publications/1/download"
    }
}
```

**Validasi:**
- `title`: required, string, max:255
- `description`: nullable, string
- `published_at`: required, date, before_or_equal:today
- `file`: required, file, mimes:pdf,doc,docx, max:10240 (10MB)

#### 6.8.4 Update Publication
```
PUT /villages/{village_id}/publications/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "title": "Laporan Tahunan Desa 2024 (Revised)",
    "description": "Laporan lengkap kegiatan desa tahun 2024 - Revisi 1",
    "published_at": "2024-12-31"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Publikasi berhasil diperbarui",
    "data": {
        "id": 1,
        "village_id": 1,
        "title": "Laporan Tahunan Desa 2024 (Revised)",
        "description": "Laporan lengkap kegiatan desa tahun 2024 - Revisi 1",
        "published_at": "2024-12-31"
    }
}
```

**Validasi:**
- `title`: sometimes, string, max:255
- `description`: nullable, string
- `published_at`: sometimes, date, before_or_equal:today

#### 6.8.5 Replace Publication File
```
POST /villages/{village_id}/publications/{id}/replace-file
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
file: (file)
```

**Response (200):**
```json
{
    "success": true,
    "message": "File publikasi berhasil diganti",
    "data": {
        "id": 1,
        "file_name": "laporan_tahunan_2024_v2.pdf",
        "file_size": 2150000,
        "file_type": "pdf",
        "download_url": "https://api.example.com/api/v1/publications/1/download"
    }
}
```

**Validasi:**
- `file`: required, file, mimes:pdf,doc,docx, max:10240 (10MB)

#### 6.8.6 Delete Publication
```
DELETE /villages/{village_id}/publications/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Publikasi berhasil dihapus"
}
```

**Business Rules:**
- File fisik di storage juga akan dihapus

#### 6.8.7 Download Publication (Public)
```
GET /publications/{id}/download
```

**Response:** File download

**Headers:**
```
Content-Type: application/pdf (atau sesuai tipe file)
Content-Disposition: attachment; filename="laporan_tahunan_2024.pdf"
```

---

### 6.9 Geospatial Data Endpoints

#### 6.9.1 Get Village Geospatial Data (Public)
```
GET /villages/{village_id}/geospatial?type=boundary
```

**Query Parameters:**
- `type`: string, filter by type (boundary, hamlet, etc)

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "village_id": 1,
            "name": "Batas Desa Makale",
            "type": "boundary",
            "geometry": {
                "type": "Polygon",
                "coordinates": [
                    [
                        [119.8234, -3.0897],
                        [119.8567, -3.0897],
                        [119.8567, -3.1234],
                        [119.8234, -3.1234],
                        [119.8234, -3.0897]
                    ]
                ]
            },
            "properties": {
                "color": "#FF0000",
                "description": "Batas wilayah administratif"
            },
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

#### 6.9.2 Create Geospatial Data
```
POST /villages/{village_id}/geospatial
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "name": "Dusun 1",
    "type": "hamlet",
    "geometry": {
        "type": "Polygon",
        "coordinates": [
            [
                [119.8234, -3.0897],
                [119.8567, -3.0897],
                [119.8567, -3.1234],
                [119.8234, -3.1234],
                [119.8234, -3.0897]
            ]
        ]
    },
    "properties": {
        "population": 500,
        "color": "#00FF00"
    }
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Data geospasial berhasil ditambahkan",
    "data": {
        "id": 2,
        "village_id": 1,
        "name": "Dusun 1",
        "type": "hamlet",
        "geometry": {...},
        "properties": {...},
        "created_by": 2,
        "created_at": "2025-01-15T00:00:00.000000Z"
    }
}
```

**Validasi:**
- `name`: required, string, max:255
- `type`: required, string, max:50
- `geometry`: required, json (valid GeoJSON)
- `properties`: nullable, json

#### 6.9.3 Update Geospatial Data
```
PUT /villages/{village_id}/geospatial/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:** (sama seperti create, semua field optional)

**Response (200):**
```json
{
    "success": true,
    "message": "Data geospasial berhasil diperbarui",
    "data": {...}
}
```

#### 6.9.4 Delete Geospatial Data
```
DELETE /villages/{village_id}/geospatial/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Data geospasial berhasil dihapus"
}
```

---

### 6.10 Thematic Maps Endpoints

#### 6.10.1 Get Village Thematic Maps (Public)
```
GET /villages/{village_id}/thematic-maps
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "village_id": 1,
            "theme_name": "UMKM",
            "description": "Sebaran usaha mikro kecil menengah",
            "icon": "shop",
            "points_count": 15,
            "created_at": "2024-01-01T00:00:00.000000Z"
        },
        {
            "id": 2,
            "village_id": 1,
            "theme_name": "Pariwisata",
            "description": "Objek wisata di desa",
            "icon": "camera",
            "points_count": 5,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

#### 6.10.2 Get Thematic Map Detail with Points (Public)
```
GET /thematic-maps/{id}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "village": {
            "id": 1,
            "name": "Desa Makale"
        },
        "theme_name": "UMKM",
        "description": "Sebaran usaha mikro kecil menengah",
        "icon": "shop",
        "points": [
            {
                "id": 1,
                "name": "Warung Makan Sari Rasa",
                "description": "Warung makan tradisional",
                "category": "Kuliner",
                "latitude": -3.0897,
                "longitude": 119.8234,
                "image_url": "https://storage.example.com/maps/point1.jpg",
                "additional_info": {
                    "owner": "Ibu Sari",
                    "phone": "081234567890"
                },
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "created_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 6.10.3 Create Thematic Map
```
POST /villages/{village_id}/thematic-maps
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "theme_name": "Ketenagakerjaan",
    "description": "Lokasi-lokasi yang menyediakan lapangan kerja",
    "icon": "briefcase"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Peta tematik berhasil ditambahkan",
    "data": {
        "id": 3,
        "village_id": 1,
        "theme_name": "Ketenagakerjaan",
        "description": "Lokasi-lokasi yang menyediakan lapangan kerja",
        "icon": "briefcase",
        "created_at": "2025-01-15T00:00:00.000000Z"
    }
}
```

**Validasi:**
- `theme_name`: required, string, max:255
- `description`: nullable, string
- `icon`: nullable, string, max:100

#### 6.10.4 Update Thematic Map
```
PUT /villages/{village_id}/thematic-maps/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:** (sama seperti create, semua field optional)

**Response (200):**
```json
{
    "success": true,
    "message": "Peta tematik berhasil diperbarui",
    "data": {...}
}
```

#### 6.10.5 Delete Thematic Map
```
DELETE /villages/{village_id}/thematic-maps/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Peta tematik berhasil dihapus"
}
```

**Business Rules:**
- Cascade delete semua map points yang terkait

---

### 6.11 Map Points Endpoints

#### 6.11.1 Create Map Point
```
POST /thematic-maps/{thematic_map_id}/points
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:**
```json
{
    "name": "Toko Kelontong Berkah",
    "description": "Toko kelontong yang menjual kebutuhan sehari-hari",
    "category": "Retail",
    "latitude": -3.0897,
    "longitude": 119.8234,
    "image_url": "https://storage.example.com/maps/toko_berkah.jpg",
    "additional_info": {
        "owner": "Pak Budi",
        "phone": "081234567891",
        "opening_hours": "07:00 - 21:00"
    }
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Titik lokasi berhasil ditambahkan",
    "data": {
        "id": 10,
        "thematic_map_id": 1,
        "name": "Toko Kelontong Berkah",
        "description": "Toko kelontong yang menjual kebutuhan sehari-hari",
        "category": "Retail",
        "latitude": -3.0897,
        "longitude": 119.8234,
        "image_url": "https://storage.example.com/maps/toko_berkah.jpg",
        "additional_info": {...},
        "created_by": 2,
        "created_at": "2025-01-15T00:00:00.000000Z"
    }
}
```

**Validasi:**
- `name`: required, string, max:255
- `description`: nullable, string
- `category`: nullable, string, max:100
- `latitude`: required, numeric, between:-90,90
- `longitude`: required, numeric, between:-180,180
- `image_url`: nullable, url
- `additional_info`: nullable, json

#### 6.11.2 Upload Map Point Image
```
POST /thematic-maps/{thematic_map_id}/points/{id}/image
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
image: (file)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Gambar titik lokasi berhasil diunggah",
    "data": {
        "image_url": "https://storage.example.com/maps/point_10_20251110.jpg"
    }
}
```

**Validasi:**
- `image`: required, file, mimes:jpeg,jpg,png, max:3072 (3MB)

#### 6.11.3 Update Map Point
```
PUT /thematic-maps/{thematic_map_id}/points/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Request Body:** (sama seperti create, semua field optional)

**Response (200):**
```json
{
    "success": true,
    "message": "Titik lokasi berhasil diperbarui",
    "data": {...}
}
```

#### 6.11.4 Delete Map Point
```
DELETE /thematic-maps/{thematic_map_id}/points/{id}
Authorization: Bearer {token}
Role: bps_admin, village_officer (own village only)
```

**Response (200):**
```json
{
    "success": true,
    "message": "Titik lokasi berhasil dihapus"
}
```

---

### 6.12 Village Modules Endpoints (BPS Admin Only)

#### 6.12.1 Get Village Modules
```
GET /villages/{village_id}/modules
Authorization: Bearer {token}
Role: bps_admin
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "village_id": 1,
            "module_name": "statistics",
            "is_enabled": true
        },
        {
            "id": 2,
            "village_id": 1,
            "module_name": "publications",
            "is_enabled": true
        },
        {
            "id": 3,
            "village_id": 1,
            "module_name": "thematic_maps",
            "is_enabled": false
        }
    ]
}
```

#### 6.12.2 Toggle Module Status
```
PUT /villages/{village_id}/modules/{module_name}/toggle
Authorization: Bearer {token}
Role: bps_admin
```

**Request Body:**
```json
{
    "is_enabled": false
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Status modul berhasil diubah",
    "data": {
        "id": 3,
        "village_id": 1,
        "module_name": "thematic_maps",
        "is_enabled": false
    }
}
```

**Validasi:**
- `is_enabled`: required, boolean

**Available Modules:**
- `statistics`: Modul data statistik
- `publications`: Modul publikasi laporan
- `thematic_maps`: Modul peta tematik
- `geospatial`: Modul data geospasial

---

### 6.13 Activity Logs Endpoints (BPS Admin Only)

#### 6.13.1 Get Activity Logs
```
GET /activity-logs?page=1&per_page=50&user_id=2&village_id=1&action=update&model_type=VillageStatistic&date_from=2024-01-01&date_to=2024-12-31
Authorization: Bearer {token}
Role: bps_admin
```

**Query Parameters:**
- `page`: integer, default: 1
- `per_page`: integer, default: 50, max: 100
- `user_id`: integer, filter by user
- `village_id`: integer, filter by village
- `action`: string, filter by action (create, update, delete)
- `model_type`: string, filter by model type
- `date_from`: date, filter from date
- `date_to`: date, filter to date

**Response (200):**
```json
{
    "success": true,
    "data": [
```json
        {
            "id": 1,
            "user": {
                "id": 2,
                "full_name": "Perangkat Desa 1",
                "role": "village_officer"
            },
            "village": {
                "id": 1,
                "name": "Desa Makale"
            },
            "action": "update",
            "model_type": "VillageStatistic",
            "model_id": 15,
            "old_data": {
                "indicator_name": "Total Penduduk",
                "value": 5000,
                "year": 2024
            },
            "new_data": {
                "indicator_name": "Total Penduduk",
                "value": 5100,
                "year": 2024
            },
            "ip_address": "192.168.1.100",
            "user_agent": "Mozilla/5.0...",
            "created_at": "2025-01-15T10:30:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 50,
        "total": 150,
        "last_page": 3
    }
}
```

#### 6.13.2 Get Activity Log Detail
```
GET /activity-logs/{id}
Authorization: Bearer {token}
Role: bps_admin
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user": {
            "id": 2,
            "username": "perangkat_desa1",
            "full_name": "Perangkat Desa 1",
            "role": "village_officer"
        },
        "village": {
            "id": 1,
            "name": "Desa Makale",
            "code": "7301012001"
        },
        "action": "update",
        "model_type": "VillageStatistic",
        "model_id": 15,
        "old_data": {
            "indicator_name": "Total Penduduk",
            "value": 5000,
            "unit": "jiwa",
            "year": 2024,
            "period": "Tahunan"
        },
        "new_data": {
            "indicator_name": "Total Penduduk",
            "value": 5100,
            "unit": "jiwa",
            "year": 2024,
            "period": "Tahunan"
        },
        "changes": [
            {
                "field": "value",
                "old": 5000,
                "new": 5100
            }
        ],
        "ip_address": "192.168.1.100",
        "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
        "created_at": "2025-01-15T10:30:00.000000Z"
    }
}
```

#### 6.13.3 Export Activity Logs
```
GET /activity-logs/export?format=csv&date_from=2024-01-01&date_to=2024-12-31
Authorization: Bearer {token}
Role: bps_admin
```

**Query Parameters:**
- `format`: string, required, in:csv,xlsx
- `date_from`: date, required
- `date_to`: date, required
- Additional filters same as Get Activity Logs

**Response:** File download (CSV or Excel)

---

### 6.14 Dashboard Statistics Endpoints

#### 6.14.1 Get BPS Admin Dashboard Stats
```
GET /dashboard/admin
Authorization: Bearer {token}
Role: bps_admin
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_villages": 10,
            "active_villages": 8,
            "inactive_villages": 2,
            "total_users": 15,
            "active_users": 12,
            "total_statistics": 250,
            "total_publications": 45,
            "total_thematic_maps": 20
        },
        "recent_activities": [
            {
                "id": 1,
                "user": "Perangkat Desa 1",
                "action": "update",
                "description": "Memperbarui data statistik Total Penduduk",
                "timestamp": "2025-01-15T10:30:00.000000Z"
            }
        ],
        "villages_statistics": [
            {
                "village_name": "Desa Makale",
                "statistics_count": 50,
                "publications_count": 10,
                "last_updated": "2025-01-15T10:30:00.000000Z"
            }
        ],
        "monthly_activities": [
            {
                "month": "2025-01",
                "statistics_created": 25,
                "statistics_updated": 15,
                "publications_uploaded": 5
            }
        ]
    }
}
```

#### 6.14.2 Get Village Officer Dashboard Stats
```
GET /dashboard/village
Authorization: Bearer {token}
Role: village_officer
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "village": {
            "id": 1,
            "name": "Desa Makale",
            "code": "7301012001"
        },
        "summary": {
            "total_statistics": 50,
            "statistics_this_year": 20,
            "total_publications": 10,
            "publications_this_year": 3,
            "thematic_maps": 2,
            "map_points": 15,
            "last_update": "2025-01-15T10:30:00.000000Z"
        },
        "recent_activities": [
            {
                "action": "create",
                "description": "Menambahkan data statistik Jumlah UMKM",
                "timestamp": "2025-01-15T10:30:00.000000Z"
            }
        ],
        "statistics_by_category": [
            {
                "category": "kependudukan",
                "count": 15
            },
            {
                "category": "ekonomi",
                "count": 10
            },
            {
                "category": "pendidikan",
                "count": 8
            }
        ],
        "profile_completeness": {
            "percentage": 85,
            "missing_fields": ["website", "logo"]
        }
    }
}
```

#### 6.14.3 Get Public Dashboard (Landing Page)
```
GET /dashboard/public
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_villages": 8,
            "total_statistics": 250,
            "total_publications": 45,
            "last_updated": "2025-01-15T10:30:00.000000Z"
        },
        "featured_villages": [
            {
                "id": 1,
                "name": "Desa Makale",
                "district": "Makale",
                "population": 5000,
                "thumbnail": "https://storage.example.com/villages/makale_thumb.jpg"
            }
        ],
        "latest_publications": [
            {
                "id": 1,
                "title": "Laporan Tahunan Desa 2024",
                "village_name": "Desa Makale",
                "published_at": "2024-12-31",
                "download_url": "https://api.example.com/api/v1/publications/1/download"
            }
        ],
        "statistics_overview": {
            "total_population": 45000,
            "total_umkm": 250,
            "literacy_rate_avg": 95.5
        }
    }
}
```

---

## 7. Keamanan dan Validasi

### 7.1 Authentication & Authorization

#### 7.1.1 Laravel Sanctum Token-Based Authentication
```php
// config/sanctum.php
'expiration' => 60 * 24, // 24 hours
'stateful' => ['localhost', 'localhost:3000', '127.0.0.1'],
'guard' => ['web'],
```

#### 7.1.2 Token Renewal Strategy
- Token berlaku selama 24 jam
- Implementasi refresh token untuk perpanjangan sesi
- Token dapat di-revoke saat logout

#### 7.1.3 Password Security
```php
// Hashing menggunakan Bcrypt
use Illuminate\Support\Facades\Hash;

// Saat membuat/update password
$user->password = Hash::make($request->password);

// Verifikasi password
if (Hash::check($request->password, $user->password)) {
    // Password correct
}
```

**Password Requirements:**
- Minimal 8 karakter
- Kombinasi huruf dan angka (recommended)
- Harus di-confirm saat registrasi/change password

#### 7.1.4 Role-Based Middleware
```php
// app/Http/Middleware/CheckRole.php
public function handle($request, Closure $next, ...$roles)
{
    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }
    
    if (!in_array(auth()->user()->role, $roles)) {
        return response()->json([
            'success' => false,
            'message' => 'Forbidden - Insufficient permissions'
        ], 403);
    }
    
    return $next($request);
}

// Penggunaan di routes
Route::middleware(['auth:sanctum', 'role:bps_admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

#### 7.1.5 Village Ownership Check
```php
// app/Policies/VillageDataPolicy.php
public function modify(User $user, $model)
{
    // BPS Admin dapat modify semua
    if ($user->role === 'bps_admin') {
        return true;
    }
    
    // Village Officer hanya untuk desa sendiri
    if ($user->role === 'village_officer') {
        return $user->village_id === $model->village_id;
    }
    
    return false;
}
```

### 7.2 Input Validation

#### 7.2.1 Validation Rules
```php
// app/Http/Requests/StoreVillageStatisticRequest.php
public function rules()
{
    return [
        'statistic_type_id' => 'required|exists:statistic_types,id',
        'indicator_name' => 'required|string|max:255',
        'value' => 'required|numeric|min:0',
        'unit' => 'nullable|string|max:50',
        'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        'period' => 'nullable|string|max:50',
        'source' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:1000',
    ];
}

public function messages()
{
    return [
        'statistic_type_id.required' => 'Tipe statistik harus dipilih',
        'statistic_type_id.exists' => 'Tipe statistik tidak valid',
        'value.required' => 'Nilai statistik harus diisi',
        'value.numeric' => 'Nilai statistik harus berupa angka',
        'year.required' => 'Tahun harus diisi',
        'year.integer' => 'Tahun harus berupa bilangan bulat',
        'year.min' => 'Tahun tidak boleh kurang dari 2000',
    ];
}
```

#### 7.2.2 Sanitization
```php
// app/Traits/Sanitizable.php
trait Sanitizable
{
    public function sanitizeInput($data)
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                // Remove XSS attempts
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
    }
}
```

#### 7.2.3 File Upload Validation
```php
// app/Http/Requests/UploadPublicationRequest.php
public function rules()
{
    return [
        'file' => [
            'required',
            'file',
            'mimes:pdf,doc,docx',
            'max:10240', // 10MB
            function ($attribute, $value, $fail) {
                // Additional validation: check file content
                $mime = $value->getMimeType();
                $allowedMimes = ['application/pdf', 'application/msword', 
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
                if (!in_array($mime, $allowedMimes)) {
                    $fail('File type not allowed');
                }
            }
        ],
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'published_at' => 'required|date|before_or_equal:today',
    ];
}
```

### 7.3 SQL Injection Prevention

Laravel Eloquent ORM dan Query Builder secara otomatis melindungi dari SQL injection dengan menggunakan prepared statements.

```php
// AMAN - menggunakan parameter binding
$users = DB::table('users')
    ->where('email', $email)
    ->get();

// AMAN - Eloquent
$user = User::where('email', $request->email)->first();

// HINDARI - raw query tanpa binding
// DB::select("SELECT * FROM users WHERE email = '$email'"); // VULNERABLE!

// Jika harus menggunakan raw query, gunakan parameter binding
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
```

### 7.4 XSS (Cross-Site Scripting) Prevention

#### 7.4.1 Output Escaping
React secara default melakukan escaping untuk mencegah XSS. Di backend:

```php
// Semua response JSON otomatis di-escape oleh Laravel
return response()->json([
    'data' => $village->name // Otomatis escaped
]);

// Untuk HTML rendering (jika ada)
{{ $variable }} // Escaped
{!! $variable !!} // Not escaped - hanya untuk trusted content
```

#### 7.4.2 Content Security Policy Headers
```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set('Content-Security-Policy', "default-src 'self'");
    
    return $response;
}
```

### 7.5 CSRF Protection

Laravel Sanctum untuk SPA tidak memerlukan CSRF token untuk API requests.

```php
// config/sanctum.php
'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
],
```

### 7.6 Rate Limiting

```php
// app/Providers/RouteServiceProvider.php
protected function configureRateLimiting()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
    
    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });
    
    RateLimiter::for('file-upload', function (Request $request) {
        return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
    });
}

// routes/api.php
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['throttle:api'])->group(function () {
    // Regular API routes
});

Route::middleware(['auth:sanctum', 'throttle:file-upload'])->group(function () {
    Route::post('/publications', [PublicationController::class, 'store']);
});
```

### 7.7 HTTPS Enforcement

```php
// app/Http/Middleware/ForceHttps.php
public function handle($request, Closure $next)
{
    if (!$request->secure() && app()->environment('production')) {
        return redirect()->secure($request->getRequestUri());
    }
    
    return $next($request);
}

// app/Providers/AppServiceProvider.php
public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### 7.8 Database Security

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true, // Enable strict mode
    'engine' => 'InnoDB',
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
],
```

**Best Practices:**
- Gunakan user database dengan privilege terbatas (hanya CRUD, tidak DROP/CREATE)
- Jangan expose database credentials di version control
- Gunakan environment variables untuk sensitive data
- Regular backup database

---

## 8. Logging dan Audit Trail

### 8.1 Activity Logging Implementation

#### 8.1.1 Observer Pattern untuk Auto-Logging
```php
// app/Observers/VillageStatisticObserver.php
class VillageStatisticObserver
{
    public function created(VillageStatistic $statistic)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'village_id' => $statistic->village_id,
            'action' => 'create',
            'model_type' => 'VillageStatistic',
            'model_id' => $statistic->id,
            'new_data' => $statistic->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    public function updated(VillageStatistic $statistic)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'village_id' => $statistic->village_id,
            'action' => 'update',
            'model_type' => 'VillageStatistic',
            'model_id' => $statistic->id,
            'old_data' => $statistic->getOriginal(),
            'new_data' => $statistic->getChanges(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    public function deleted(VillageStatistic $statistic)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'village_id' => $statistic->village_id,
            'action' => 'delete',
            'model_type' => 'VillageStatistic',
            'model_id' => $statistic->id,
            'old_data' => $statistic->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// app/Providers/EventServiceProvider.php
public function boot()
{
    VillageStatistic::observe(VillageStatisticObserver::class);
    Publication::observe(PublicationObserver::class);
    // Register other observers
}
```

#### 8.1.2 Log Levels
- **INFO**: Normal operations (create, read)
- **WARNING**: Update operations, suspicious activities
- **ERROR**: Failed operations, exceptions
- **CRITICAL**: Security breaches, unauthorized access attempts

#### 8.1.3 Laravel Log Configuration
```php
// config/logging.php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
    
    'activity' => [
        'driver' => 'daily',
        'path' => storage_path('logs/activity.log'),
        'level' => 'info',
        'days' => 90, // Keep activity logs for 90 days
    ],
    
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'warning',
        'days' => 180, // Keep security logs for 180 days
    ],
],
```

### 8.2 Log Retention Policy

- **Activity Logs**: 90 hari di database, archive ke file setelah 90 hari
- **Security Logs**: 180 hari
- **Application Logs**: 14 hari
- **Database Backups**: 30 hari

### 8.3 Log Querying Performance

```sql
-- Index untuk performa query log
CREATE INDEX idx_activity_logs_user_created ON activity_logs(user_id, created_at);
CREATE INDEX idx_activity_logs_village_created ON activity_logs(village_id, created_at);
CREATE INDEX idx_activity_logs_action_created ON activity_logs(action, created_at);

-- Partitioning untuk table activity_logs (optional untuk data besar)
ALTER TABLE activity_logs 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

---

## 9. Penanganan Error

### 9.1 Error Response Format

```json
{
    "success": false,
    "message": "Error message yang user-friendly",
    "errors": {
        "field_name": [
            "Error message for this field"
        ]
    },
    "error_code": "ERROR_CODE",
    "debug": {
        "file": "/path/to/file.php",
        "line": 123,
        "trace": "..." // Hanya di development
    }
}
```

### 9.2 Custom Exception Handler

```php
// app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($request->wantsJson() || $request->is('api/*')) {
        return $this->handleApiException($request, $exception);
    }
    
    return parent::render($request, $exception);
}

protected function handleApiException($request, Throwable $exception)
{
    $status = 500;
    $message = 'Internal Server Error';
    $errors = null;
    $errorCode = 'INTERNAL_ERROR';
    
    if ($exception instanceof ValidationException) {
        $status = 422;
        $message = 'Validation failed';
        $errors = $exception->errors();
        $errorCode = 'VALIDATION_ERROR';
    } elseif ($exception instanceof AuthenticationException) {
        $status = 401;
        $message = 'Unauthenticated';
        $errorCode = 'UNAUTHENTICATED';
    } elseif ($exception instanceof AuthorizationException) {
        $status = 403;
        $message = 'Forbidden';
        $errorCode = 'FORBIDDEN';
    } elseif ($exception instanceof ModelNotFoundException) {
        $status = 404;
        $message = 'Resource not found';
        $errorCode = 'NOT_FOUND';
    } elseif ($exception instanceof ThrottleRequestsException) {
        $status = 429;
        $message = 'Too many requests';
        $errorCode = 'RATE_LIMIT_EXCEEDED';
    }
    
    $response = [
        'success' => false,
        'message' => $message,
        'error_code' => $errorCode,
    ];
    
    if ($errors) {
        $response['errors'] = $errors;
    }
    
    // Add debug info in development
    if (config('app.debug')) {
        $response['debug'] = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
    }
    
    return response()->json($response, $status);
}
```

### 9.3 Custom Exceptions

```php
// app/Exceptions/VillageAccessDeniedException.php
class VillageAccessDeniedException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki akses ke desa ini',
            'error_code' => 'VILLAGE_ACCESS_DENIED'
        ], 403);
    }
}

// app/Exceptions/FileUploadException.php
class FileUploadException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'FILE_UPLOAD_ERROR'
        ], 400);
    }
}
```

### 9.4 Error Codes Reference

| Error Code | HTTP Status | Description |
|------------|-------------|-------------|
| VALIDATION_ERROR | 422 | Validasi input gagal |
| UNAUTHENTICATED | 401 | Tidak terautentikasi |
| FORBIDDEN | 403 | Tidak memiliki permission |
| NOT_FOUND | 404 | Resource tidak ditemukan |
| VILLAGE_ACCESS_DENIED | 403 | Tidak punya akses ke desa |
| FILE_UPLOAD_ERROR | 400 | Error saat upload file |
| FILE_TOO_LARGE | 413 | File terlalu besar |
| INVALID_FILE_TYPE | 400 | Tipe file tidak valid |
| RATE_LIMIT_EXCEEDED | 429 | Terlalu banyak request |
| DATABASE_ERROR | 500 | Error database |
| INTERNAL_ERROR | 500 | Error server internal |

---

## 10. Pertimbangan Performa

### 10.1 Database Query Optimization

#### 10.1.1 Eager Loading
```php
// BAD - N+1 Query Problem
$statistics = VillageStatistic::all();
foreach ($statistics as $stat) {
    echo $stat->village->name; // Query untuk setiap iterasi
}

// GOOD - Eager Loading
$statistics = VillageStatistic::with(['village', 'statisticType', 'creator'])->get();
foreach ($statistics as $stat) {
    echo $stat->village->name; // Tidak ada query tambahan
}
```

#### 10.1.2 Pagination
```php
// Selalu gunakan pagination untuk list data
$villages = Village::with('profile')
    ->where('is_active', true)
    ->orderBy('display_order')
    ->paginate(15);
```

#### 10.1.3 Select Specific Columns
```php
// BAD - Select all columns
$users = User::all();

// GOOD - Select only needed columns
$users = User::select('id', 'full_name', 'email', 'role')->get();
```

#### 10.1.4 Indexes Usage
Pastikan query menggunakan index yang sudah dibuat:

```sql
-- Cek query yang tidak menggunakan index
EXPLAIN SELECT * FROM village_statistics WHERE village_id = 1;

-- Cek index usage
SHOW INDEX FROM village_statistics;
```

### 10.2 Caching Strategy

#### 10.2.1 Cache Configuration
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

#### 10.2.2 Cache Implementation
```php
// app/Services/VillageService.php
class VillageService
{
    public function getActiveVillages()
    {
        return Cache::remember('villages.active', 3600, function () {
            return Village::with('profile')
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get();
        });
    }
    
    public function getVillageStatistics($villageId, $year)
    {
        $cacheKey = "village.{$villageId}.statistics.{$year}";
        
        return Cache::remember($cacheKey, 1800, function () use ($villageId, $year) {
            return VillageStatistic::with(['statisticType', 'creator'])
                ->where('village_id', $villageId)
                ->where('year', $year)
                ->get();
        });
    }
    
    public function clearVillageCache($villageId)
    {
        Cache::forget('villages.active');
        Cache::forget("village.{$villageId}.profile");
        // Clear related caches
    }
}
```

#### 10.2.3 Cache Invalidation
```php
// app/Observers/VillageStatisticObserver.php
public function created(VillageStatistic $statistic)
{
    // Clear cache after create
    Cache::forget("village.{$statistic->village_id}.statistics.{$statistic->year}");
    Cache::forget("village.{$statistic->village_id}.statistics.summary");
}

public function updated(VillageStatistic $statistic)
{
    // Clear cache after update
    Cache::forget("village.{$statistic->village_id}.statistics.{$statistic->year}");
}
```

### 10.3 File Storage Optimization

#### 10.3.1 Storage Configuration
```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    's3' => [ // Optional untuk production
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
```php
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
    ],
],
```

#### 10.3.2 File Organization
```
storage/app/public/
├── publications/
│   ├── village_1/
│   │   ├── 2024/
│   │   │   ├── laporan_tahunan.pdf
│   │   │   └── laporan_semester.pdf
│   │   └── 2025/
│   └── village_2/
├── images/
│   ├── villages/
│   │   ├── logos/
│   │   └── photos/
│   └── map_points/
└── temp/
    └── imports/
```

#### 10.3.3 File Upload Handler
```php
// app/Services/FileUploadService.php
class FileUploadService
{
    public function uploadPublicationFile($file, $villageId)
    {
        $year = date('Y');
        $directory = "publications/village_{$villageId}/{$year}";
        
        // Generate unique filename
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $fullFilename = "{$filename}.{$extension}";
        
        // Store file
        $path = $file->storeAs($directory, $fullFilename, 'public');
        
        return [
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $extension,
            'url' => Storage::url($path)
        ];
    }
    
    public function deleteFile($path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }
        return false;
    }
    
    public function getFileUrl($path)
    {
        return Storage::url($path);
    }
}
```

### 10.4 API Response Optimization

#### 10.4.1 Response Compression
```php
// app/Http/Middleware/CompressResponse.php
class CompressResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        if ($this->shouldCompress($request, $response)) {
            $response->header('Content-Encoding', 'gzip');
            $response->setContent(gzencode($response->getContent(), 9));
        }
        
        return $response;
    }
    
    protected function shouldCompress($request, $response)
    {
        // Check if client accepts gzip
        if (!str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            return false;
        }
        
        // Don't compress if already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }
        
        // Only compress text responses
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'json') || 
               str_contains($contentType, 'text');
    }
}
```

#### 10.4.2 JSON Response Optimization
```php
// app/Http/Resources/VillageResource.php
class VillageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'district' => $this->district,
            'is_active' => $this->is_active,
            
            // Conditional loading
            'profile' => $this->whenLoaded('profile', function () {
                return new VillageProfileResource($this->profile);
            }),
            
            'statistics_count' => $this->when(
                $request->input('include_counts'),
                $this->statistics()->count()
            ),
            
            // Tidak include timestamp jika tidak perlu
            $this->mergeWhen($request->input('include_timestamps'), [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]),
        ];
    }
}
```

### 10.5 Database Connection Pool

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => 'InnoDB',
    
    // Connection pool settings
    'options' => [
        PDO::ATTR_PERSISTENT => true, // Use persistent connections
        PDO::ATTR_TIMEOUT => 5,
    ],
],
```

### 10.6 Queue untuk Operasi Berat

```php
// app/Jobs/ProcessStatisticsImport.php
class ProcessStatisticsImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $filePath;
    protected $villageId;
    protected $userId;
    
    public function __construct($filePath, $villageId, $userId)
    {
        $this->filePath = $filePath;
        $this->villageId = $villageId;
        $this->userId = $userId;
    }
    
    public function handle()
    {
        // Process large CSV/Excel import
        $data = Excel::toArray(new StatisticsImport, $this->filePath);
        
        foreach ($data[0] as $row) {
            VillageStatistic::create([
                'village_id' => $this->villageId,
                'statistic_type_id' => $this->getStatisticTypeId($row['type']),
                'indicator_name' => $row['indicator'],
                'value' => $row['value'],
                'year' => $row['year'],
                'created_by' => $this->userId,
            ]);
        }
        
        // Clean up temp file
        Storage::delete($this->filePath);
    }
}

// Controller usage
public function import(Request $request, $villageId)
{
    $file = $request->file('file');
    $path = $file->store('temp/imports');
    
    ProcessStatisticsImport::dispatch($path, $villageId, auth()->id());
    
    return response()->json([
        'success' => true,
        'message' => 'Import sedang diproses. Anda akan menerima notifikasi saat selesai.'
    ]);
}
```

### 10.7 Monitoring dan Profiling

#### 10.7.1 Query Monitoring
```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if (app()->environment('local')) {
        DB::listen(function ($query) {
            if ($query->time > 1000) { // Query lebih dari 1 detik
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]);
            }
        });
    }
}
```

#### 10.7.2 Performance Metrics
```php
// app/Http/Middleware/MeasurePerformance.php
class MeasurePerformance
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $response = $next($request);
        
        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;
        
        // Log performance metrics
        Log::info('Request Performance', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'memory_used' => round($memoryUsed / 1024, 2) . 'KB',
            'query_count' => count(DB::getQueryLog()),
        ]);
        
        // Add headers for debugging
        if (app()->environment('local')) {
            $response->headers->set('X-Execution-Time', round($executionTime * 1000, 2) . 'ms');
            $response->headers->set('X-Memory-Used', round($memoryUsed / 1024, 2) . 'KB');
        }
        
        return $response;
    }
}
```

---

## 11. Testing Strategy

### 11.1 Unit Testing

```php
// tests/Unit/VillageStatisticTest.php
use Tests\TestCase;
use App\Models\VillageStatistic;
use App\Models\Village;
use App\Models\User;

class VillageStatisticTest extends TestCase
{
    public function test_can_create_statistic()
    {
        $user = User::factory()->create(['role' => 'bps_admin']);
        $village = Village::factory()->create();
        
        $statistic = VillageStatistic::create([
            'village_id' => $village->id,
            'statistic_type_id' => 1,
            'indicator_name' => 'Test Indicator',
            'value' => 100,
            'year' => 2024,
            'created_by' => $user->id,
        ]);
        
        $this->assertDatabaseHas('village_statistics', [
            'indicator_name' => 'Test Indicator',
            'value' => 100,
        ]);
    }
    
    public function test_statistic_belongs_to_village()
    {
        $statistic = VillageStatistic::factory()->create();
        
        $this->assertInstanceOf(Village::class, $statistic->village);
    }
}
```

### 11.2 Feature Testing

```php
// tests/Feature/VillageStatisticsApiTest.php
use Tests\TestCase;
use App\Models\User;
use App\Models\Village;
use Laravel\Sanctum\Sanctum;

class VillageStatisticsApiTest extends TestCase
{
    public function test_bps_admin_can_create_statistic()
    {
        $admin = User::factory()->create(['role' => 'bps_admin']);
        $village = Village::factory()->create();
        
        Sanctum::actingAs($admin);
        
        $response = $this->postJson("/api/v1/villages/{$village->id}/statistics", [
            'statistic_type_id' => 1,
            'indicator_name' => 'Test Indicator',
            'value' => 100,
            'year' => 2024,
        ]);
        
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Data statistik berhasil ditambahkan',
                 ]);
    }
    
    public function test_village_officer_cannot_access_other_village()
    {
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();
        
        $officer = User::factory()->create([
            'role' => 'village_officer',
            'village_id' => $village1->id,
        ]);
        
        Sanctum::actingAs($officer);
        
        $response = $this->postJson("/api/v1/villages/{$village2->id}/statistics", [
            'statistic_type_id' => 1,
            'indicator_name' => 'Test',
            'value' => 100,
            'year' => 2024,
        ]);
        
        $response->assertStatus(403);
    }
    
    public function test_guest_can_view_public_statistics()
    {
        $village = Village::factory()->create(['is_active' => true]);
        VillageStatistic::factory()->count(5)->create([
            'village_id' => $village->id,
        ]);
        
        $response = $this->getJson("/api/v1/villages/{$village->id}/statistics");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'indicator_name',
                             'value',
                             'year',
                         ]
                     ],
                     'meta'
                 ]);
    }
}
```

### 11.3 Integration Testing

```php
// tests/Integration/StatisticsImportTest.php
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Village;
use Laravel\Sanctum\Sanctum;

class StatisticsImportTest extends TestCase
{
    public function test_can_import_statistics_from_csv()
    {
        $admin = User::factory()->create(['role' => 'bps_admin']);
        $village = Village::factory()->create();
        
        Sanctum::actingAs($admin);
        
        $csv = UploadedFile::fake()->createWithContent('statistics.csv', 
            "statistic_type_code,indicator_name,value,unit,year\n" .
            "POPULATION_TOTAL,Total Penduduk,5000,jiwa,2024\n" .
            "POPULATION_MALE,Penduduk Laki-laki,2500,jiwa,2024"
        );
        
        $response = $this->postJson(
            "/api/v1/villages/{$village->id}/statistics/import",
            ['file' => $csv]
        );
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'total_rows',
                         'imported',
                         'failed',
                     ]
                 ]);
        
        $this->assertDatabaseCount('village_statistics', 2);
    }
}
```

---

## 12. Deployment Configuration

### 12.1 Environment Configuration

#### 12.1.1 Production Environment (.env)
```env
APP_NAME="Sistem Informasi Desa Cantik"
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://desacantik-torut.go.id

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=desa_cantik_prod
DB_USERNAME=desa_cantik_user
DB_PASSWORD=secure_password_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@bpstorut.go.id
MAIL_PASSWORD=mail_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@bpstorut.go.id
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=public

SANCTUM_STATEFUL_DOMAINS=desacantik-torut.go.id
SESSION_DOMAIN=.desacantik-torut.go.id
```

### 12.2 Apache Virtual Host Configuration

```apache
<VirtualHost *:80>
    ServerName desacantik-torut.go.id
    ServerAlias www.desacantik-torut.go.id
    
    # Redirect to HTTPS
    Redirect permanent / https://desacantik-torut.go.id/
</VirtualHost>

<VirtualHost *:443>
    ServerName desacantik-torut.go.id
    ServerAlias www.desacantik-torut.go.id
    
    DocumentRoot /var/www/desa-cantik/public
    
    <Directory /var/www/desa-cantik/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/desacantik-torut.crt
    SSLCertificateKeyFile /etc/ssl/private/desacantik-torut.key
    SSLCertificateChainFile /etc/ssl/certs/desacantik-torut-chain.crt
    
    # Security Headers
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/desa-cantik-error.log
    CustomLog ${APACHE_LOG_DIR}/desa-cantik-access.log combined
    
    # PHP Configuration
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
</VirtualHost>
```

### 12.3 Database Backup Script

```bash
#!/bin/bash
# backup-database.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/desa-cantik"
DB_NAME="desa_cantik_prod"
DB_USER="desa_cantik_user"
DB_PASS="secure_password_here"

# Create backup directory if not exists
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 30 days of backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -type f -mtime +30 -delete

# Log backup
echo "$(date): Database backup completed - db_backup_$DATE.sql.gz" >> $BACKUP_DIR/backup.log
```

### 12.4 Cron Jobs

```cron
# /etc/cron.d/desa-cantik

# Laravel Scheduler
* * * * * www-data cd /var/www/desa-cantik && php artisan schedule:run >> /dev/null 2>&1

# Database Backup - Daily at 2 AM
0 2 * * * root /usr/local/bin/backup-database.sh

# Clear old logs - Weekly on Sunday at 3 AM
0 3 * * 0 www-data cd /var/www/desa-cantik && php artisan log:clear --days=14

# Queue Worker (if not using supervisor)
# * * * * * www-data cd /var/www/desa-cantik && php artisan queue:work --stop-when-empty

# Clear expired tokens - Daily at 4 AM
0 4 * * * www-data cd /var/www/desa-cantik && php artisan sanctum:prune-expired --hours=24
```

### 12.5 Supervisor Configuration for Queue Workers

```ini
# /etc/supervisor/conf.d/desa-cantik-worker.conf

[program:desa-cantik-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/desa-cantik/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/desa-cantik/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 13. Maintenance dan Monitoring

### 13.1 Health Check Endpoint

```php
// routes/api.php
Route::get('/health', function () {
    $checks = [
        'database' => false,
        'redis' => false,
        'storage' => false,
    ];
    
    // Check database
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Exception $e) {
        Log::error('Database health check failed: ' . $e->getMessage());
    }
    
    // Check Redis
    try {
        Redis::ping();
        $checks['redis'] = true;
    } catch (\Exception $e) {
        Log::error('Redis health check failed: ' . $e->getMessage());
    }
    
    // Check storage
    try {
        $checks['storage'] = Storage::disk('public')->exists('test.txt') || 
                             Storage::disk('public')->put('test.txt', 'test');
    } catch (\Exception $e) {
        Log::error('Storage health check failed: ' . $e->getMessage());
    }
    
    $allHealthy = array_reduce($checks, fn($carry, $item) => $carry && $item, true);
    
    return response()->json([
        'status' => $allHealthy ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
    ], $allHealthy ? 200 : 503);
});
```

### 13.2 Maintenance Mode

```bash
# Enable maintenance mode
php artisan down --message="Sistem sedang dalam maintenance" --retry=60

# Allow specific IPs during maintenance
php artisan down --allow=192.168.1.100 --allow=192.168.1.101

# Disable maintenance mode
php artisan up
```

### 13.3 Scheduled Tasks

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Backup database daily at 2 AM
    $schedule->command('backup:database')->dailyAt('02:00');
    
    // Clear old activity logs (older than 90 days)
    $schedule->call(function () {
        ActivityLog::where('created_at', '<', now()->subDays(90))->delete();
    })->weekly();
    
    // Clear expired tokens daily
    $schedule->command('sanctum:prune-expired --hours=24')->daily();
    
    // Generate monthly statistics report
    $schedule->call(function () {
        // Generate and email monthly report
    })->monthlyOn(1, '08:00');
    
    // Clear temp files older than 1 day
    $schedule->call(function () {
        Storage::disk('public')->deleteDirectory('temp');
    })->daily();
}
```

---

## 14. Documentation dan API Versioning

### 14.1 API Versioning Strategy

API menggunakan URI versioning dengan format: `/api/v{version}/`

**Current Version:** v1

**Version Lifecycle:**
- v1: Current (Active development)
- Future versions akan maintain backward compatibility selama minimal 6 bulan

### 14.2 Changelog

#### Version 1.0.0 (Initial Release)
- Authentication & Authorization system
- Village management
- Statistics management
- Publications management
- Geospatial data management
- Thematic maps management
- Activity logging
- Dashboard statistics

### 14.3 API Documentation

Dokumentasi API dapat diakses melalui:
- **Swagger/OpenAPI**: `https://desacantik-torut.go.id/api/documentation`
- **Postman Collection**: Tersedia di repository project

---

## 15. Lampiran

### 15.1 Database ER Diagram (Extended)

```
Legend:
PK = Primary Key
FK = Foreign Key
UK = Unique Key
```

### 15.2 API Request/Response Examples

Tersedia dalam file terpisah: `api-examples.md`

### 15.3 Glossary

| Term | Definition |
|------|------------|
| BPS | Badan Pusat Statistik |
| Desa Cantik | Desa Cinta Statistik |
| CRUD | Create, Read, Update, Delete |
| JWT | JSON Web Token |
| ORM | Object-Relational Mapping |
| XSS | Cross-Site Scripting |
| CSRF | Cross-Site Request Forgery |
| SDI | Satu Data Indonesia |

---

## 16. Kontak dan Support

**Tim Pengembang:** Tim 4 Kelas 3SI1  
**Institusi:** Politeknik Statistika STIS  
**Client:** BPS Kabupaten Toraja Utara

**Product Owner:** Dannar Kurniawan Ajie Prasetya, S.Tr.Stat.  
**Proxy Product Owners:**
- Ainur Rahma, S.Tr.Stat.
- Antonius Parupang, A.Md.Stat.

---

**Dokumen ini akan diperbarui seiring dengan perkembangan sistem.**

**Last Updated:** 10 November 2025  
**Version:** 1.0