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
                'role_name' => 'bps_admin',
                'display_name' => 'BPS Admin',
                'description' => 'Administrator dari BPS yang mengelola seluruh sistem',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'village_officer',
                'display_name' => 'Perangkat Desa',
                'description' => 'Perangkat desa yang mengelola data desa masing-masing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'guest',
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
