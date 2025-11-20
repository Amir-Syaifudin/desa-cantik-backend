<?php

namespace Database\Seeders;

use App\Models\StatisticType;
use App\Models\Village;
use App\Models\VillageStatistic;
use Illuminate\Database\Seeder;

class VillageStatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds village statistics based on the mock data removed from frontend in commit 1fcb1add6a8
     * Mock data included: RT/RW counts, Aparatur counts, Population distribution
     */
    public function run(): void
    {
        $this->command->info('Seeding village statistics...');

        $village = Village::first();
        $admin = \App\Models\User::where('email', 'admin@bps.go.id')->first();

        if (!$village || !$admin) {
            $this->command->warn('No villages or admin user found. Run VillageSeeder and UserSeeder first.');
            return;
        }

        // Create or get statistic types for the mock data
        $statisticTypes = [
            [
                'name' => 'Jumlah RT',
                'code' => 'jumlah_rt',
                'category' => 'Wilayah & Pemerintahan',
                'description' => 'Jumlah Rukun Tetangga',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Jumlah RW',
                'code' => 'jumlah_rw',
                'category' => 'Wilayah & Pemerintahan',
                'description' => 'Jumlah Rukun Warga',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Jumlah Kepala Desa',
                'code' => 'jumlah_kepala_desa',
                'category' => 'Wilayah & Pemerintahan',
                'description' => 'Jumlah Kepala Desa',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Jumlah Sekretaris Desa',
                'code' => 'jumlah_sekdes',
                'category' => 'Wilayah & Pemerintahan',
                'description' => 'Jumlah Sekretaris Desa',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Penduduk Laki-laki',
                'code' => 'penduduk_laki',
                'category' => 'Kependudukan',
                'description' => 'Jumlah penduduk laki-laki',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Penduduk Perempuan',
                'code' => 'penduduk_perempuan',
                'category' => 'Kependudukan',
                'description' => 'Jumlah penduduk perempuan',
                'is_active' => true,
                'display_order' => 6,
            ],
        ];

        $types = [];
        foreach ($statisticTypes as $typeData) {
            $types[] = StatisticType::firstOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        // Create statistics data based on mock data from VillageDetail.jsx
        $statistics = [
            // RT/RW data for 2024 and 2025
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[0]->id, // Jumlah RT
                'indicator_name' => 'Jumlah RT',
                'year' => 2024,
                'value' => 10,
                'unit' => 'Unit',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[0]->id, // Jumlah RT
                'indicator_name' => 'Jumlah RT',
                'year' => 2025,
                'value' => 11,
                'unit' => 'Unit',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[1]->id, // Jumlah RW
                'indicator_name' => 'Jumlah RW',
                'year' => 2024,
                'value' => 3,
                'unit' => 'Unit',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[1]->id, // Jumlah RW
                'indicator_name' => 'Jumlah RW',
                'year' => 2025,
                'value' => 3,
                'unit' => 'Unit',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            // Aparatur data
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[2]->id, // Kepala Desa
                'indicator_name' => 'Jumlah Kepala Desa',
                'year' => 2025,
                'value' => 1,
                'unit' => 'Orang',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[3]->id, // Sekretaris Desa
                'indicator_name' => 'Jumlah Sekretaris Desa',
                'year' => 2025,
                'value' => 1,
                'unit' => 'Orang',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            // Population distribution
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[4]->id, // Laki-laki
                'indicator_name' => 'Penduduk Laki-laki',
                'year' => 2025,
                'value' => 800,
                'unit' => 'Jiwa',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
            [
                'village_id' => $village->id,
                'statistic_type_id' => $types[5]->id, // Perempuan
                'indicator_name' => 'Penduduk Perempuan',
                'year' => 2025,
                'value' => 780,
                'unit' => 'Jiwa',
                'source' => 'Kantor Lembang',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($statistics as $stat) {
            VillageStatistic::create($stat);
        }

        $this->command->info('✓ Created ' . count($statistics) . ' village statistics');
    }
}
