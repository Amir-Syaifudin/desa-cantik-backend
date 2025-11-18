<?php

namespace Tests\Traits;

use App\Models\GeospatialData;
use App\Models\ThematicMap;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;

/**
 * Trait for creating test data that was previously mocked in frontend.
 * 
 * Mock data removed from frontend:
 * - MOCK_DESA_LIST (UbahPasswordAdminBPS.jsx)
 * - MOCK_GEOSPATIAL (PetaTematikAdmin.jsx)
 * - MOCK_LAYERS (PetaTematikAdmin.jsx)
 */
trait CreatesTestData
{
    /**
     * Create village officers data (replaces MOCK_DESA_LIST)
     * 
     * Original mock data:
     * - { id: 'desa_sukamaju', name: 'Perangkat Desa Suka Maju' }
     * - { id: 'desa_makmur', name: 'Perangkat Desa Makmur Jaya' }
     */
    protected function createVillageOfficers(int $count = 2): array
    {
        $villages = Village::factory()->count($count)->create([
            'is_visible' => true,
        ]);

        $role = UserRole::firstWhere('role_name', UserRole::VILLAGE_OFFICER)
            ?? UserRole::factory()->villageOfficer()->create();

        $officers = [];
        foreach ($villages as $index => $village) {
            $officers[] = User::factory()->create([
                'role_id' => $role->id,
                'village_id' => $village->id,
                'full_name' => 'Perangkat Desa ' . $village->name,
                'email' => strtolower(str_replace(' ', '', $village->name)) . '@desacantik.id',
                'is_active' => true,
            ]);
        }

        return $officers;
    }

    /**
     * Create geospatial data (replaces MOCK_GEOSPATIAL)
     * 
     * Original mock data:
     * - { id: 'geo001', name: 'Batas Wilayah Desa A', type: 'Polygon', source: 'data_desa_a.geojson' }
     * - { id: 'geo002', name: 'Titik Lokasi Sekolah', type: 'Point', source: 'data_sekolah.geojson' }
     * - { id: 'geo003', name: 'Jaringan Sungai', type: 'LineString', source: 'data_sungai.geojson' }
     */
    protected function createGeospatialData(Village $village): array
    {
        $geoData = [];

        // Polygon - Batas Wilayah
        $geoData['boundary'] = GeospatialData::factory()->create([
            'desa_id' => $village->id,
            'description' => 'Batas Wilayah Desa',
            'geometry_type' => 'Polygon',
            'geojson_data' => [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => ['name' => 'Batas Desa'],
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
        ]);

        // Point - Lokasi Sekolah
        $geoData['schools'] = GeospatialData::factory()->create([
            'desa_id' => $village->id,
            'description' => 'Titik Lokasi Sekolah',
            'geometry_type' => 'Point',
            'geojson_data' => [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => ['name' => 'SD Negeri 1', 'type' => 'Sekolah Dasar'],
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [119.90, -2.97],
                        ],
                    ],
                    [
                        'type' => 'Feature',
                        'properties' => ['name' => 'SMP Negeri 1', 'type' => 'Sekolah Menengah Pertama'],
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [119.905, -2.972],
                        ],
                    ],
                ],
            ],
        ]);

        // LineString - Jaringan Sungai
        $geoData['river'] = GeospatialData::factory()->create([
            'desa_id' => $village->id,
            'description' => 'Jaringan Sungai',
            'geometry_type' => 'LineString',
            'geojson_data' => [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => ['name' => 'Sungai Utama'],
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
        ]);

        return $geoData;
    }

    /**
     * Create thematic maps (replaces MOCK_LAYERS)
     * 
     * Original mock data:
     * - { id: 'layer01', name: 'Peta Kepadatan Penduduk', geoId: 'geo001', color: '#FF0000' }
     * - { id: 'layer02', name: 'Peta Fasilitas Pendidikan', geoId: 'geo002', color: '#0000FF' }
     */
    protected function createThematicMaps(Village $village, ?array $geospatialData = null): array
    {
        $geospatialData ??= $this->createGeospatialData($village);

        $maps = [];

        // Peta Kepadatan Penduduk (using boundary polygon)
        $maps['population'] = ThematicMap::factory()->create([
            'desa_id' => $village->id,
            'map_name' => 'Peta Kepadatan Penduduk',
            'map_type' => 'Demografi',
            'description' => 'Visualisasi kepadatan penduduk per wilayah',
            'layer_config' => [
                'color' => '#FF0000',
                'opacity' => 0.7,
                'geospatial_data_id' => $geospatialData['boundary']->id,
            ],
            'is_active' => true,
        ]);

        // Peta Fasilitas Pendidikan (using school points)
        $maps['education'] = ThematicMap::factory()->create([
            'desa_id' => $village->id,
            'map_name' => 'Peta Fasilitas Pendidikan',
            'map_type' => 'Pendidikan',
            'description' => 'Lokasi sekolah dan fasilitas pendidikan',
            'layer_config' => [
                'color' => '#0000FF',
                'opacity' => 0.8,
                'geospatial_data_id' => $geospatialData['schools']->id,
            ],
            'is_active' => true,
        ]);

        return $maps;
    }

    /**
     * Create complete test village with geospatial and thematic maps
     */
    protected function createTestVillageWithMaps(): array
    {
        $village = Village::factory()->create(['is_visible' => true]);
        $geospatialData = $this->createGeospatialData($village);
        $thematicMaps = $this->createThematicMaps($village, $geospatialData);

        return [
            'village' => $village,
            'geospatial' => $geospatialData,
            'maps' => $thematicMaps,
        ];
    }
}
