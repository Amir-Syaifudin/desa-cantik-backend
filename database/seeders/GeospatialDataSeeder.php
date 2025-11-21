<?php

namespace Database\Seeders;

use App\Models\GeospatialData;
use App\Models\Village;
use Illuminate\Database\Seeder;

class GeospatialDataSeeder extends Seeder
{
    /**
     * Seed geospatial data for villages.
     * This data was previously mocked in PetaTematikAdmin.jsx as MOCK_GEOSPATIAL
     */
    public function run(): void
    {
        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        $geospatialTemplates = [
            [
                'description' => 'Batas Wilayah Desa',
                'geometry_type' => 'Polygon',
                'geojson_data' => [
                    'type' => 'FeatureCollection',
                    'features' => [
                        [
                            'type' => 'Feature',
                            'properties' => [
                                'name' => 'Batas Desa',
                                'color' => '#FF0000',
                            ],
                            'geometry' => [
                                'type' => 'Polygon',
                                'coordinates' => [
                                    [
                                        [119.89, -2.98],
                                        [119.91, -2.98],
                                        [119.91, -2.96],
                                        [119.89, -2.96],
                                        [119.89, -2.98],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'description' => 'Titik Lokasi Sekolah',
                'geometry_type' => 'Point',
                'geojson_data' => [
                    'type' => 'FeatureCollection',
                    'features' => [
                        [
                            'type' => 'Feature',
                            'properties' => [
                                'name' => 'SD Negeri 1',
                                'type' => 'Sekolah Dasar',
                            ],
                            'geometry' => [
                                'type' => 'Point',
                                'coordinates' => [119.90, -2.97],
                            ],
                        ],
                        [
                            'type' => 'Feature',
                            'properties' => [
                                'name' => 'SMP Negeri 1',
                                'type' => 'Sekolah Menengah Pertama',
                            ],
                            'geometry' => [
                                'type' => 'Point',
                                'coordinates' => [119.905, -2.972],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'description' => 'Jaringan Sungai',
                'geometry_type' => 'LineString',
                'geojson_data' => [
                    'type' => 'FeatureCollection',
                    'features' => [
                        [
                            'type' => 'Feature',
                            'properties' => [
                                'name' => 'Sungai Utama',
                                'width' => 10,
                            ],
                            'geometry' => [
                                'type' => 'LineString',
                                'coordinates' => [
                                    [119.89, -2.97],
                                    [119.895, -2.975],
                                    [119.90, -2.98],
                                    [119.91, -2.985],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($villages as $village) {
            foreach ($geospatialTemplates as $template) {
                GeospatialData::updateOrCreate(
                    [
                        'village_id' => $village->id,
                        'description' => $template['description'],
                    ],
                    [
                        'geometry_type' => $template['geometry_type'],
                        'geojson_data' => $template['geojson_data'],
                    ]
                );
            }
        }

        $this->command->info('✓ Geospatial data seeded successfully');
    }
}
