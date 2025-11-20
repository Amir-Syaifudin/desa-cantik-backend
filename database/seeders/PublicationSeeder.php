<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds publications based on the mock data that was removed from frontend in commit 1fcb1add6a8
     */
    public function run(): void
    {
        $this->command->info('Seeding publications...');

        // Get first village and admin user for testing
        $village = Village::first();
        $admin = User::where('email', 'admin@bps.go.id')->first();

        if (!$village || !$admin) {
            $this->command->warn('Village or admin user not found. Run VillageSeeder and UserSeeder first.');
            return;
        }

        $publications = [
            [
                'desa_id' => $village->id,
                'title' => 'Statistik Pelabuhan Perikanan 2024',
                'description' => 'Data statistik terkini pelabuhan perikanan.',
                'category' => 'Statistik Desa',
                'published_at' => '2025-11-07',
                'status' => 'Terverifikasi',
                'uploaded_by' => $admin->id,
                'file_url' => '/storage/publications/statistik-pelabuhan-perikanan-2024.pdf',
                'file_name' => 'statistik-pelabuhan-perikanan-2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '2.5 MB',
                'file_size_bytes' => 2621440,
            ],
            [
                'desa_id' => $village->id,
                'title' => 'Statistik Pendaratan Ikan Tradisional 2024',
                'description' => 'Data pendaratan ikan nelayan tradisional.',
                'category' => 'Ekonomi Lokal',
                'published_at' => '2025-11-07',
                'status' => 'Terverifikasi',
                'uploaded_by' => $admin->id,
                'file_url' => '/storage/publications/statistik-pendaratan-ikan-2024.pdf',
                'file_name' => 'statistik-pendaratan-ikan-2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '1.8 MB',
                'file_size_bytes' => 1887436,
            ],
            [
                'desa_id' => $village->id,
                'title' => 'Benchmark Indeks Konstruksi 2023',
                'description' => 'Analisis indeks harga konstruksi.',
                'category' => 'Infrastruktur',
                'published_at' => '2025-10-31',
                'status' => 'Terverifikasi',
                'uploaded_by' => $admin->id,
                'file_url' => '/storage/publications/benchmark-indeks-konstruksi-2023.pdf',
                'file_name' => 'benchmark-indeks-konstruksi-2023.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '3.2 MB',
                'file_size_bytes' => 3355443,
            ],
            [
                'desa_id' => $village->id,
                'title' => 'Cerita Data Statistik Indonesia',
                'description' => 'Kajian pendidikan dan pekerjaan pemuda.',
                'category' => 'Pendidikan',
                'published_at' => '2025-10-31',
                'status' => 'Terverifikasi',
                'uploaded_by' => $admin->id,
                'file_url' => '/storage/publications/cerita-data-statistik-indonesia.pdf',
                'file_name' => 'cerita-data-statistik-indonesia.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '4.1 MB',
                'file_size_bytes' => 4299161,
            ],
            [
                'desa_id' => $village->id,
                'title' => 'Statistik Impor Bulanan',
                'description' => 'Laporan bulanan data impor.',
                'category' => 'Ekonomi Lokal',
                'published_at' => '2024-10-31',
                'status' => 'Terverifikasi',
                'uploaded_by' => $admin->id,
                'file_url' => '/storage/publications/statistik-impor-bulanan-2024.pdf',
                'file_name' => 'statistik-impor-bulanan-2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '1.5 MB',
                'file_size_bytes' => 1572864,
            ],
            [
                'desa_id' => $village->id,
                'title' => 'Direktori Industri Manufaktur',
                'description' => 'Direktori perusahaan industri manufaktur.',
                'category' => 'Ekonomi Lokal',
                'published_at' => '2024-09-30',
                'status' => 'Terverifikasi',
                'uploaded_by' => $admin->id,
                'file_url' => '/storage/publications/direktori-industri-manufaktur.pdf',
                'file_name' => 'direktori-industri-manufaktur.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '5.7 MB',
                'file_size_bytes' => 5976883,
            ],
        ];

        foreach ($publications as $pub) {
            Publication::create($pub);
        }

        $this->command->info('✓ Created ' . count($publications) . ' publications');
    }
}
