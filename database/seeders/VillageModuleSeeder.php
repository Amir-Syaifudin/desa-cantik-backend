<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds village modules based on mock data removed from frontend in commit 1fcb1add6a8
     */
    public function run(): void
    {
        $this->command->info('Seeding village modules...');

        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Run VillageSeeder first.');
            return;
        }

        // Module definitions from ModulDesaAdmin.jsx mock data
        $moduleDefinitions = [
            [
                'name' => 'Demografi',
                'description' => 'Pendataan kondisi demografi.',
                'status' => 'active',
            ],
            [
                'name' => 'Pendidikan',
                'description' => 'Pendataan kondisi pendidikan.',
                'status' => 'inactive',
            ],
            [
                'name' => 'Ekonomi',
                'description' => 'Pendataan kondisi ekonomi.',
                'status' => 'active',
            ],
            [
                'name' => 'Kesehatan',
                'description' => 'Pendataan kondisi kesehatan.',
                'status' => 'active',
            ],
            [
                'name' => 'Pertanian',
                'description' => 'Pendataan kondisi pertanian.',
                'status' => 'active',
            ],
        ];

        $count = 0;
        foreach ($villages as $village) {
            foreach ($moduleDefinitions as $moduleDef) {
                Module::firstOrCreate(
                    [
                        'village_id' => $village->id,
                        'name' => $moduleDef['name'],
                    ],
                    [
                        'status' => $moduleDef['status'],
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✓ Created/verified {$count} village modules for " . $villages->count() . " villages");
    }
}
