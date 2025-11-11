<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'admin_bps',
                'display_name' => 'Admin BPS',
                'description' => 'Administrator dari BPS yang mengelola seluruh sistem',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'perangkat_desa',
                'display_name' => 'Perangkat Desa',
                'description' => 'Perangkat desa yang mengelola data desa masing-masing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'masyarakat',
                'display_name' => 'Masyarakat Umum',
                'description' => 'Masyarakat umum yang dapat melihat data publik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('user_roles')->insert($roles);
        $this->command->info('✅ 3 roles created');
    }
}
