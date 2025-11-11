<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatisticTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $types = [
            [
                'name' => 'Jumlah Penduduk',
                'code' => 'POPULATION_TOTAL',
                'category' => 'kependudukan',
                'description' => 'Total jumlah penduduk desa',
                'display_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Jumlah Penduduk Laki-laki',
                'code' => 'POPULATION_MALE',
                'category' => 'kependudukan',
                'description' => 'Jumlah penduduk laki-laki',
                'display_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Jumlah Penduduk Perempuan',
                'code' => 'POPULATION_FEMALE',
                'category' => 'kependudukan',
                'description' => 'Jumlah penduduk perempuan',
                'display_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Jumlah Kepala Keluarga',
                'code' => 'HOUSEHOLDS',
                'category' => 'kependudukan',
                'description' => 'Jumlah kepala keluarga',
                'display_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Angka Melek Huruf',
                'code' => 'LITERACY_RATE',
                'category' => 'pendidikan',
                'description' => 'Persentase penduduk yang dapat membaca dan menulis',
                'display_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Jumlah UMKM',
                'code' => 'UMKM_TOTAL',
                'category' => 'ekonomi',
                'description' => 'Total UMKM yang terdaftar',
                'display_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tingkat Kemiskinan',
                'code' => 'POVERTY_RATE',
                'category' => 'kesejahteraan',
                'description' => 'Persentase penduduk miskin',
                'display_order' => 7,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Luas Lahan Pertanian',
                'code' => 'AGRICULTURAL_LAND',
                'category' => 'ekonomi',
                'description' => 'Luas lahan pertanian dalam hektar',
                'display_order' => 8,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Jumlah Fasilitas Kesehatan',
                'code' => 'HEALTH_FACILITIES',
                'category' => 'kesehatan',
                'description' => 'Total fasilitas kesehatan (Puskesmas, Posyandu, dll)',
                'display_order' => 9,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Jumlah Fasilitas Pendidikan',
                'code' => 'EDUCATION_FACILITIES',
                'category' => 'pendidikan',
                'description' => 'Total fasilitas pendidikan (SD, SMP, SMA, dll)',
                'display_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('statistic_types')->upsert($types, ['code']);

        $this->command?->info('Default statistic types seeded.');
    }
}
