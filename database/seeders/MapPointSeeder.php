<?php

namespace Database\Seeders;

use App\Models\MapPoint;
use App\Models\ThematicMap;
use App\Models\Village;
use Illuminate\Database\Seeder;

class MapPointSeeder extends Seeder
{
    /**
     * Seed map points for thematic maps
     */
    public function run(): void
    {
        $villages = Village::all();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        foreach ($villages as $village) {
            $thematicMaps = ThematicMap::where('village_id', $village->id)->get();

            if ($thematicMaps->isEmpty()) {
                $this->command->warn("No thematic maps found for village {$village->name}. Please run ThematicMapSeeder first.");
                continue;
            }

            foreach ($thematicMaps as $map) {
                $points = [];

                if (str_contains($map->map_name, 'Pendidikan')) {
                    $points = [
                        [
                            'name' => 'SD Negeri 1',
                            'description' => 'Sekolah Dasar Negeri 1',
                            'category' => 'Sekolah Dasar',
                            'latitude' => -2.97 + ($village->id * 0.01),
                            'longitude' => 119.90 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/2196F3/FFFFFF?text=SD',
                            'metadata' => ['type' => 'SD', 'students' => 250],
                        ],
                        [
                            'name' => 'SMP Negeri 1',
                            'description' => 'Sekolah Menengah Pertama Negeri 1',
                            'category' => 'SMP',
                            'latitude' => -2.972 + ($village->id * 0.01),
                            'longitude' => 119.905 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/1976D2/FFFFFF?text=SMP',
                            'metadata' => ['type' => 'SMP', 'students' => 180],
                        ],
                        [
                            'name' => 'SMA Negeri 1',
                            'description' => 'Sekolah Menengah Atas Negeri 1',
                            'category' => 'SMA',
                            'latitude' => -2.968 + ($village->id * 0.01),
                            'longitude' => 119.91 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/0D47A1/FFFFFF?text=SMA',
                            'metadata' => ['type' => 'SMA', 'students' => 150],
                        ],
                        [
                            'name' => 'SD Negeri 2',
                            'description' => 'Sekolah Dasar Negeri 2',
                            'category' => 'Sekolah Dasar',
                            'latitude' => -2.975 + ($village->id * 0.01),
                            'longitude' => 119.895 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/2196F3/FFFFFF?text=SD',
                            'metadata' => ['type' => 'SD', 'students' => 200],
                        ],
                    ];
                } elseif (str_contains($map->map_name, 'Kesehatan')) {
                    $points = [
                        [
                            'name' => 'Puskesmas',
                            'description' => 'Pusat Kesehatan Masyarakat',
                            'category' => 'Puskesmas',
                            'latitude' => -2.975 + ($village->id * 0.01),
                            'longitude' => 119.895 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/4CAF50/FFFFFF?text=PKM',
                            'metadata' => ['type' => 'Puskesmas', 'staff' => 15],
                        ],
                        [
                            'name' => 'Posyandu Melati',
                            'description' => 'Pos Pelayanan Terpadu',
                            'category' => 'Posyandu',
                            'latitude' => -2.98 + ($village->id * 0.01),
                            'longitude' => 119.90 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/81C784/FFFFFF?text=PY',
                            'metadata' => ['type' => 'Posyandu', 'cadres' => 5],
                        ],
                        [
                            'name' => 'Posyandu Mawar',
                            'description' => 'Pos Pelayanan Terpadu',
                            'category' => 'Posyandu',
                            'latitude' => -2.97 + ($village->id * 0.01),
                            'longitude' => 119.905 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/81C784/FFFFFF?text=PY',
                            'metadata' => ['type' => 'Posyandu', 'cadres' => 4],
                        ],
                    ];
                } elseif (str_contains($map->map_name, 'Penduduk')) {
                    $points = [
                        [
                            'name' => 'Dusun 1',
                            'description' => 'Wilayah dengan kepadatan penduduk tinggi',
                            'category' => 'Dusun',
                            'latitude' => -2.97 + ($village->id * 0.01),
                            'longitude' => 119.90 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/FF0000/FFFFFF?text=D1',
                            'metadata' => ['population' => 1200, 'density' => 'Tinggi'],
                        ],
                        [
                            'name' => 'Dusun 2',
                            'description' => 'Wilayah dengan kepadatan penduduk sedang',
                            'category' => 'Dusun',
                            'latitude' => -2.975 + ($village->id * 0.01),
                            'longitude' => 119.905 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/FB6A4A/FFFFFF?text=D2',
                            'metadata' => ['population' => 800, 'density' => 'Sedang'],
                        ],
                        [
                            'name' => 'Dusun 3',
                            'description' => 'Wilayah dengan kepadatan penduduk rendah',
                            'category' => 'Dusun',
                            'latitude' => -2.98 + ($village->id * 0.01),
                            'longitude' => 119.895 + ($village->id * 0.01),
                            'icon_url' => 'https://placehold.co/40x40/FEE5D9/FFFFFF?text=D3',
                            'metadata' => ['population' => 500, 'density' => 'Rendah'],
                        ],
                    ];
                }

                foreach ($points as $point) {
                    MapPoint::updateOrCreate(
                        [
                            'thematic_map_id' => $map->id,
                            'name' => $point['name'],
                        ],
                        $point
                    );
                }
            }
        }

        $this->command->info('✓ ' . MapPoint::count() . ' map points created');
    }
}

