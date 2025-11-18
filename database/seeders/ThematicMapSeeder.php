<?php

namespace Database\Seeders;

use App\Models\ThematicMap;
use App\Models\Village;
use Illuminate\Database\Seeder;

class ThematicMapSeeder extends Seeder
{
    /**
     * Seed thematic maps for villages.
     * This data was previously mocked in PetaTematikAdmin.jsx as MOCK_LAYERS
     */
    public function run(): void
    {
        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            // Create thematic maps for each village
            $maps = [
                [
                    'desa_id' => $village->id,
                    'map_name' => 'Peta Kepadatan Penduduk',
                    'map_type' => 'Demografi',
                    'description' => 'Visualisasi kepadatan penduduk per wilayah',
                    'layer_config' => [
                        'color' => '#FF0000',
                        'opacity' => 0.7,
                        'legend' => [
                            'title' => 'Kepadatan Penduduk',
                            'unit' => 'jiwa/km²',
                            'ranges' => [
                                ['min' => 0, 'max' => 500, 'color' => '#FEE5D9', 'label' => 'Rendah'],
                                ['min' => 500, 'max' => 1000, 'color' => '#FCAE91', 'label' => 'Sedang'],
                                ['min' => 1000, 'max' => 2000, 'color' => '#FB6A4A', 'label' => 'Tinggi'],
                                ['min' => 2000, 'max' => null, 'color' => '#CB181D', 'label' => 'Sangat Tinggi'],
                            ],
                        ],
                    ],
                    'is_active' => true,
                ],
                [
                    'desa_id' => $village->id,
                    'map_name' => 'Peta Fasilitas Pendidikan',
                    'map_type' => 'Pendidikan',
                    'description' => 'Lokasi sekolah dan fasilitas pendidikan',
                    'layer_config' => [
                        'color' => '#0000FF',
                        'opacity' => 0.8,
                        'legend' => [
                            'title' => 'Fasilitas Pendidikan',
                            'items' => [
                                ['type' => 'SD', 'color' => '#2196F3', 'label' => 'Sekolah Dasar'],
                                ['type' => 'SMP', 'color' => '#1976D2', 'label' => 'SMP'],
                                ['type' => 'SMA', 'color' => '#0D47A1', 'label' => 'SMA'],
                            ],
                        ],
                    ],
                    'is_active' => true,
                ],
                [
                    'desa_id' => $village->id,
                    'map_name' => 'Peta Fasilitas Kesehatan',
                    'map_type' => 'Kesehatan',
                    'description' => 'Lokasi fasilitas kesehatan dan sarana medis',
                    'layer_config' => [
                        'color' => '#00FF00',
                        'opacity' => 0.75,
                        'legend' => [
                            'title' => 'Fasilitas Kesehatan',
                            'items' => [
                                ['type' => 'Puskesmas', 'color' => '#4CAF50', 'label' => 'Puskesmas'],
                                ['type' => 'Posyandu', 'color' => '#81C784', 'label' => 'Posyandu'],
                                ['type' => 'Klinik', 'color' => '#2E7D32', 'label' => 'Klinik'],
                            ],
                        ],
                    ],
                    'is_active' => true,
                ],
            ];

            foreach ($maps as $mapData) {
                ThematicMap::updateOrCreate(
                    [
                        'desa_id' => $mapData['desa_id'],
                        'map_name' => $mapData['map_name'],
                    ],
                    $mapData
                );
            }
        }

        $this->command->info('✓ Thematic maps seeded successfully');
    }
}
