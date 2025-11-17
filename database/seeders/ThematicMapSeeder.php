<?php

namespace Database\Seeders;

use App\Models\GeospatialData;
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
            // Get geospatial data for this village
            $boundaryGeo = GeospatialData::where('desa_id', $village->id)
                ->where('geometry_type', 'Polygon')
                ->first();

            $schoolGeo = GeospatialData::where('desa_id', $village->id)
                ->where('geometry_type', 'Point')
                ->first();

            $maps = [];

            if ($boundaryGeo) {
                $maps[] = [
                    'village_id' => $village->id,
                    'geospatial_data_id' => $boundaryGeo->id,
                    'name' => 'Peta Kepadatan Penduduk',
                    'description' => 'Visualisasi kepadatan penduduk per wilayah',
                    'color' => '#FF0000',
                    'legend' => json_encode([
                        'title' => 'Kepadatan Penduduk',
                        'unit' => 'jiwa/km²',
                        'ranges' => [
                            ['min' => 0, 'max' => 500, 'color' => '#FEE5D9', 'label' => 'Rendah'],
                            ['min' => 500, 'max' => 1000, 'color' => '#FCAE91', 'label' => 'Sedang'],
                            ['min' => 1000, 'max' => 2000, 'color' => '#FB6A4A', 'label' => 'Tinggi'],
                            ['min' => 2000, 'max' => null, 'color' => '#CB181D', 'label' => 'Sangat Tinggi'],
                        ],
                    ]),
                    'is_active' => true,
                ];
            }

            if ($schoolGeo) {
                $maps[] = [
                    'village_id' => $village->id,
                    'geospatial_data_id' => $schoolGeo->id,
                    'name' => 'Peta Fasilitas Pendidikan',
                    'description' => 'Lokasi sekolah dan fasilitas pendidikan',
                    'color' => '#0000FF',
                    'legend' => json_encode([
                        'title' => 'Fasilitas Pendidikan',
                        'items' => [
                            ['type' => 'SD', 'color' => '#2196F3', 'label' => 'Sekolah Dasar'],
                            ['type' => 'SMP', 'color' => '#1976D2', 'label' => 'SMP'],
                            ['type' => 'SMA', 'color' => '#0D47A1', 'label' => 'SMA'],
                        ],
                    ]),
                    'is_active' => true,
                ];
            }

            foreach ($maps as $mapData) {
                ThematicMap::updateOrCreate(
                    [
                        'village_id' => $mapData['village_id'],
                        'name' => $mapData['name'],
                    ],
                    $mapData
                );
            }
        }

        $this->command->info('✓ Thematic maps seeded successfully');
    }
}
