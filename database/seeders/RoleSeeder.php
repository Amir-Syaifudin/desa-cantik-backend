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
                'display_name' => 'Admin BPS',
                'description' => 'BPS Administrator with full access to all villages and admin functions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'village_officer',
                'display_name' => 'Perangkat Desa',
                'description' => 'Village Officer with access only to their assigned village',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
        $this->command->info('✅ 2 roles created (bps_admin, village_officer)');
        $this->command->info('Note: Guest/public users do not require a role or authentication');
    }
}
