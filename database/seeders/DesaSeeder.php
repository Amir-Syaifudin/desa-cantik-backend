<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesaSeeder extends Seeder
{
    public function run(): void
    {
        $desa = [
            [
                'kode_desa' => '7316010001',
                'nama_desa' => 'Nonongan Selatan',
                'kecamatan' => 'Rantepao',
                'kabupaten' => 'Toraja Utara',
                'provinsi' => 'Sulawesi Selatan',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_desa' => '7316010002',
                'nama_desa' => 'Rindingbatu',
                'kecamatan' => 'Rantepao',
                'kabupaten' => 'Toraja Utara',
                'provinsi' => 'Sulawesi Selatan',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('desa')->insert($desa);
        $this->command->info('✅ 2 desa created');
    }
}
