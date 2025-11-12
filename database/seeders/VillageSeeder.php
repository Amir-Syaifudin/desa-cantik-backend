<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            [
                'village_code' => '7316010001',
                'name' => 'Nonongan Selatan',
                'kecamatan' => 'Rantepao',
                'kabupaten' => 'Toraja Utara',
                'provinsi' => 'Sulawesi Selatan',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'village_code' => '7316010002',
                'name' => 'Rindingbatu',
                'kecamatan' => 'Rantepao',
                'kabupaten' => 'Toraja Utara',
                'provinsi' => 'Sulawesi Selatan',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($villages as $village) {
            Village::updateOrCreate(
                ['village_code' => $village['village_code']],
                $village
            );
        }

        $this->command->info('✓ Villages seeded successfully');
    }
}