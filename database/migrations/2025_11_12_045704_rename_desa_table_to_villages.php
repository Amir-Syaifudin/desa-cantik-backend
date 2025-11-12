<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename the table first
        Schema::rename('desa', 'villages');

        // Rename columns using Laravel's schema builder (database-agnostic)
        Schema::table('villages', function (Blueprint $table) {
            $table->renameColumn('kode_desa', 'village_code');
            $table->renameColumn('nama_desa', 'name');
        });
    }

    public function down(): void
    {
        // Reverse the column renames
        Schema::table('villages', function (Blueprint $table) {
            $table->renameColumn('village_code', 'kode_desa');
            $table->renameColumn('name', 'nama_desa');
        });

        // Rename the table back
        Schema::rename('villages', 'desa');
    }
};
