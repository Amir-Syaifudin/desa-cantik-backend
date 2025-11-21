<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageModuleSeeder extends Seeder
{
    public function run(): void
    {
        $villages = Village::all();

        // Daftar modul standar untuk setiap desa
        $defaultModules = [
            ['module_name' => 'Publikasi', 'is_active' => true],
            ['module_name' => 'Statistik', 'is_active' => true],
            ['module_name' => 'Peta Tematik', 'is_active' => true],
            ['module_name' => 'Layanan Mandiri', 'is_active' => false],
        ];

        foreach ($villages as $village) {
            foreach ($defaultModules as $module) {
                Module::updateOrCreate(
                    [
                        'desa_id' => $village->id,
                        'module_name' => $module['module_name'], // GANTI 'nama_desa' JADI 'module_name'
                    ],
                    [
                        'is_active' => $module['is_active'],
                        'activated_at' => $module['is_active'] ? now() : null,
                    ]
                );
            }
        }

        $this->command->info('✓ Village modules seeded successfully');
    }
}