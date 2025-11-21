<?php

namespace Database\Seeders;

use App\Models\ThematicMap;
use App\Models\Village;
use App\Models\GeospatialData;
use Illuminate\Database\Seeder;

class ThematicMapSeeder extends Seeder
{
    public function run(): void
    {
        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            // 1. Ambil ID Geospatial Data yang sudah di-seed sebelumnya
            // Pastikan deskripsi di sini SAMA PERSIS dengan yang ada di GeospatialDataSeeder
            $geoBatas = GeospatialData::where('village_id', $village->id)->where('description', 'Batas Wilayah Desa')->first();
            $geoSekolah = GeospatialData::where('village_id', $village->id)->where('description', 'Titik Lokasi Sekolah')->first();
            $geoSungai = GeospatialData::where('village_id', $village->id)->where('description', 'Jaringan Sungai')->first();

            $maps = [
                [
                    'village_id' => $village->id, // Sesuai migration thematic_maps
                    'map_name' => 'Peta Kepadatan Penduduk',
                    'map_type' => 'Demografi',
                    'description' => 'Visualisasi kepadatan penduduk per wilayah',
                    // PENTING: Ini kolom baru yang kita tambahkan
                    'geospatial_data_id' => $geoBatas?->id, 
                    'layer_config' => [
                        'color' => '#FF0000',
                        'opacity' => 0.5,
                        'legend' => [
                            'title' => 'Kepadatan',
                            'items' => [['label' => 'Tinggi', 'color' => '#FF0000']]
                        ]
                    ],
                    'is_active' => true,
                ],
                [
                    'village_id' => $village->id,
                    'map_name' => 'Peta Sebaran Sekolah',
                    'map_type' => 'Pendidikan',
                    'description' => 'Lokasi sekolah dan fasilitas pendidikan',
                    'geospatial_data_id' => $geoSekolah?->id,
                    'layer_config' => [
                        'color' => '#0000FF',
                        'opacity' => 1.0,
                    ],
                    'is_active' => true,
                ],
                [
                    'village_id' => $village->id,
                    'map_name' => 'Peta Potensi Perairan',
                    'map_type' => 'Lingkungan',
                    'description' => 'Jalur aliran sungai utama',
                    'geospatial_data_id' => $geoSungai?->id,
                    'layer_config' => [
                        'color' => '#00FF00',
                        'opacity' => 0.8,
                    ],
                    'is_active' => true,
                ],
            ];

            foreach ($maps as $mapData) {
                // Gunakan updateOrCreate agar tidak duplikat saat seed ulang
                ThematicMap::updateOrCreate(
                    [
                        'village_id' => $mapData['village_id'],
                        'map_name' => $mapData['map_name'],
                    ],
                    $mapData
                );
            }
        }

        $this->command->info('✓ Thematic maps seeded and linked successfully');
    }
}