<?php

namespace Database\Seeders;

use App\Models\Village;
use App\Models\VillageProfile;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            [
                'kode_desa' => '7316010001', // GANTI KEY
                'nama_desa' => 'Nonongan Selatan', // GANTI KEY
                'kecamatan' => 'Rantepao',
                'kabupaten' => 'Toraja Utara',
                'provinsi' => 'Sulawesi Selatan',
                'logo_url' => 'https://placehold.co/400x400/1C6EA4/FFFFFF?text=Nonongan',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'profile' => [
                    'deskripsi' => 'Desa Nonongan Selatan merupakan desa binaan dengan fokus pada pengembangan wisata adat.',
                    'foto_url' => 'https://placehold.co/800x600/a3e635/ffffff?text=Nonongan+Selatan',
                    'thumbnail_url' => 'https://placehold.co/600x400/a3e635/ffffff?text=Nonongan+Selatan',
                    'area' => 12.5,
                    'population' => 8542,
                    'households' => 2145,
                    'male_population' => 4285,
                    'female_population' => 4257,
                    'address' => 'Jalan Poros Nonongan, Rantepao',
                    'phone' => '0413-1234567',
                    'email' => 'nonongan@desacantik.id',
                    'website' => 'https://nonongan.desacantik.id',
                    'is_featured' => true,
                ],
            ],
            [
                'kode_desa' => '7316010002', // GANTI KEY
                'nama_desa' => 'Rindingbatu', // GANTI KEY
                'kecamatan' => 'Rantepao',
                'kabupaten' => 'Toraja Utara',
                'provinsi' => 'Sulawesi Selatan',
                'logo_url' => 'https://placehold.co/400x400/33A1E0/FFFFFF?text=Rindingbatu',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'profile' => [
                    'deskripsi' => 'Desa Rindingbatu dikenal dengan potensi budaya dan kerajinan bambu.',
                    'foto_url' => 'https://placehold.co/800x600/33A1E0/ffffff?text=Rindingbatu',
                    'thumbnail_url' => 'https://placehold.co/600x400/33A1E0/ffffff?text=Rindingbatu',
                    'area' => 10.3,
                    'population' => 6234,
                    'households' => 1856,
                    'male_population' => 3120,
                    'female_population' => 3114,
                    'address' => 'Jalan Poros Tikunna Malenong, Rantepao',
                    'phone' => '0413-7654321',
                    'email' => 'rindingbatu@desacantik.id',
                    'website' => 'https://rindingbatu.desacantik.id',
                    'is_featured' => false,
                ],
            ],
        ];

        foreach ($villages as $village) {
            $profileData = $village['profile'] ?? null;
            unset($village['profile']);

            // Gunakan 'kode_desa'
            $villageModel = Village::updateOrCreate(
                ['kode_desa' => $village['kode_desa']],
                $village
            );

            if ($profileData) {
                // Gunakan 'desa_id'
                $profileData['desa_id'] = $villageModel->id;
                $profileData['population_density'] = $profileData['population_density']
                    ?? ($profileData['area'] ? round($profileData['population'] / $profileData['area'], 2) : null);

                VillageProfile::updateOrCreate(
                    ['desa_id' => $villageModel->id],
                    $profileData
                );
            }
        }

        $this->command->info('✓ Villages seeded successfully');
    }
}