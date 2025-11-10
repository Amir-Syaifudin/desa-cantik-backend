<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin_bps',
                'email' => 'admin@bps.go.id',
                'password' => Hash::make('password123'),
                'full_name' => 'Administrator BPS Toraja Utara',
                'phone_number' => '081234567890',
                'role_id' => 1,
                'desa_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'perangkat_nonongan',
                'email' => 'nonongan@desacantik.id',
                'password' => Hash::make('password123'),
                'full_name' => 'Kepala Desa Nonongan Selatan',
                'phone_number' => '081234567891',
                'role_id' => 2,
                'desa_id' => 1,
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'perangkat_rindingbatu',
                'email' => 'rindingbatu@desacantik.id',
                'password' => Hash::make('password123'),
                'full_name' => 'Kepala Desa Rindingbatu',
                'phone_number' => '081234567892',
                'role_id' => 2,
                'desa_id' => 2,
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
        $this->command->info('✅ 3 users created');
    }
}
