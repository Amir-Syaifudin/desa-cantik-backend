<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('desa', 'villages');
        
        DB::statement('ALTER TABLE villages CHANGE kode_desa village_code VARCHAR(20)');
        DB::statement('ALTER TABLE villages CHANGE nama_desa name VARCHAR(255)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE villages CHANGE village_code kode_desa VARCHAR(20)');
        DB::statement('ALTER TABLE villages CHANGE name nama_desa VARCHAR(255)');
        
        Schema::rename('villages', 'desa');
    }
};