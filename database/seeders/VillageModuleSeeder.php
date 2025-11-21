<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageModuleSeeder extends Seeder
{
    /**
     * Seed village modules (activate modules per village)
     */
    public function run(): void
    {
        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        $modules = [
            'Publikasi',
            'Statistik',
            'Peta Tematik',
        ];

        foreach ($villages as $village) {
            foreach ($modules as $moduleName) {
                Module::updateOrCreate(
                    [
                        'village_id' => $village->id,
                        'name' => $moduleName,
                    ],
                    [
                        'status' => 'active',
                    ]
                );
            }
        }

        $this->command->info('✓ ' . Module::count() . ' village modules created');
    }
}

