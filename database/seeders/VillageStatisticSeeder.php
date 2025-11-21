<?php

namespace Database\Seeders;

use App\Models\StatisticType;
use App\Models\User;
use App\Models\Village;
use App\Models\VillageStatistic;
use Illuminate\Database\Seeder;

class VillageStatisticSeeder extends Seeder
{
    /**
     * Seed village statistics for multiple years
     */
    public function run(): void
    {
        $villages = Village::all();
        $admin = User::where('email', 'admin@bps.go.id')->first();
        $statisticTypes = StatisticType::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        if (!$admin) {
            $this->command->warn('Admin user not found. Please run UserSeeder first.');
            return;
        }

        if ($statisticTypes->isEmpty()) {
            $this->command->warn('No statistic types found. Please run StatisticTypeSeeder first.');
            return;
        }

        $years = [2022, 2023, 2024];
        $statisticData = [
            'POPULATION_TOTAL' => ['base' => 5000, 'growth' => 150],
            'POPULATION_MALE' => ['base' => 2500, 'growth' => 75],
            'POPULATION_FEMALE' => ['base' => 2500, 'growth' => 75],
            'HOUSEHOLDS' => ['base' => 1200, 'growth' => 30],
            'LITERACY_RATE' => ['base' => 85.5, 'growth' => 0.5],
            'UMKM_TOTAL' => ['base' => 150, 'growth' => 10],
            'POVERTY_RATE' => ['base' => 12.5, 'growth' => -0.3],
            'AGRICULTURAL_LAND' => ['base' => 500, 'growth' => 5],
            'HEALTH_FACILITIES' => ['base' => 3, 'growth' => 0],
            'EDUCATION_FACILITIES' => ['base' => 5, 'growth' => 0],
        ];

        $units = [
            'POPULATION_TOTAL' => 'jiwa',
            'POPULATION_MALE' => 'jiwa',
            'POPULATION_FEMALE' => 'jiwa',
            'HOUSEHOLDS' => 'KK',
            'LITERACY_RATE' => '%',
            'UMKM_TOTAL' => 'unit',
            'POVERTY_RATE' => '%',
            'AGRICULTURAL_LAND' => 'hektar',
            'HEALTH_FACILITIES' => 'unit',
            'EDUCATION_FACILITIES' => 'unit',
        ];

        foreach ($villages as $village) {
            // Nonongan Selatan lebih besar dari Rindingbatu
            $villageMultiplier = $village->id === 1 ? 1.0 : 0.8;

            foreach ($years as $year) {
                foreach ($statisticTypes as $statType) {
                    $code = $statType->code;
                    if (!isset($statisticData[$code])) {
                        continue;
                    }

                    $data = $statisticData[$code];
                    $yearOffset = $year - 2022;
                    $value = ($data['base'] * $villageMultiplier) + ($data['growth'] * $yearOffset * $villageMultiplier);

                    // Round based on type
                    if (in_array($code, ['LITERACY_RATE', 'POVERTY_RATE'])) {
                        $value = round($value, 2);
                    } else {
                        $value = round($value);
                    }

                    VillageStatistic::updateOrCreate(
                        [
                            'desa_id' => $village->id,
                            'statistic_type_id' => $statType->id,
                            'year' => $year,
                        ],
                        [
                            'indicator_name' => $statType->name,
                            'value' => $value,
                            'unit' => $units[$code] ?? null,
                            'period' => 'Tahunan',
                            'source' => 'BPS Kabupaten Toraja Utara',
                            'notes' => "Data tahun {$year}",
                            'created_by' => $admin->id,
                            'updated_by' => $admin->id,
                        ]
                    );
                }
            }
        }

        $this->command->info('✓ ' . VillageStatistic::count() . ' village statistics created');
    }
}

