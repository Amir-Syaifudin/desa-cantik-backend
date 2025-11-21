<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Seed publications for testing
     */
    public function run(): void
    {
        $villages = Village::all();
        $admin = User::where('email', 'admin@bps.go.id')->first();

        if ($villages->isEmpty()) {
            $this->command->warn('No villages found. Please run VillageSeeder first.');
            return;
        }

        if (!$admin) {
            $this->command->warn('Admin user not found. Please run UserSeeder first.');
            return;
        }

        $publications = [
            [
                'title' => 'Laporan Statistik Desa Tahun 2024',
                'description' => 'Laporan lengkap statistik desa untuk tahun 2024 meliputi data kependudukan, ekonomi, dan sosial.',
                'category' => 'Laporan Tahunan',
                'file_url' => 'https://example.com/publications/laporan-2024.pdf',
                'file_path' => 'publications/laporan-2024.pdf',
                'file_name' => 'laporan-statistik-desa-2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '2.5 MB',
                'file_size_bytes' => 2621440,
                'published_at' => now()->subMonths(2),
            ],
            [
                'title' => 'Profil Desa dan Potensi Wisata',
                'description' => 'Dokumen profil desa yang mencakup sejarah, budaya, dan potensi wisata yang dapat dikembangkan.',
                'category' => 'Profil Desa',
                'file_url' => 'https://example.com/publications/profil-desa.pdf',
                'file_path' => 'publications/profil-desa.pdf',
                'file_name' => 'profil-desa-wisata.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '1.8 MB',
                'file_size_bytes' => 1887436,
                'published_at' => now()->subMonths(1),
            ],
            [
                'title' => 'Data Kependudukan dan Demografi 2023',
                'description' => 'Data lengkap kependudukan, struktur umur, dan demografi desa untuk tahun 2023.',
                'category' => 'Data Statistik',
                'file_url' => 'https://example.com/publications/kependudukan-2023.pdf',
                'file_path' => 'publications/kependudukan-2023.pdf',
                'file_name' => 'data-kependudukan-2023.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '1.2 MB',
                'file_size_bytes' => 1258291,
                'published_at' => now()->subMonths(6),
            ],
            [
                'title' => 'Rencana Pembangunan Jangka Menengah Desa (RPJMDes) 2024-2029',
                'description' => 'Dokumen perencanaan pembangunan desa untuk periode 2024-2029.',
                'category' => 'Perencanaan',
                'file_url' => 'https://example.com/publications/rpjmdes-2024-2029.pdf',
                'file_path' => 'publications/rpjmdes-2024-2029.pdf',
                'file_name' => 'rpjmdes-2024-2029.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '3.2 MB',
                'file_size_bytes' => 3355443,
                'published_at' => now()->subMonths(3),
            ],
            [
                'title' => 'Laporan APBDes Tahun 2024',
                'description' => 'Laporan Anggaran Pendapatan dan Belanja Desa untuk tahun 2024.',
                'category' => 'Keuangan',
                'file_url' => 'https://example.com/publications/apbdes-2024.pdf',
                'file_path' => 'publications/apbdes-2024.pdf',
                'file_name' => 'apbdes-2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => '1.5 MB',
                'file_size_bytes' => 1572864,
                'published_at' => now()->subMonths(4),
            ],
        ];

        foreach ($villages as $village) {
            foreach ($publications as $pub) {
                Publication::updateOrCreate(
                    [
                        'desa_id' => $village->id,
                        'title' => $pub['title'],
                    ],
                    array_merge($pub, [
                        'desa_id' => $village->id,
                        'uploaded_by' => $admin->id,
                    ])
                );
            }
        }

        $this->command->info('✓ ' . Publication::count() . ' publications created');
    }
}

