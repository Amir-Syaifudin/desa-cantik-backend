<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa', function (Blueprint $table) {
            $table->id();
            $table->string('kode_desa', 20)->unique()->nullable();
            $table->string('nama_desa', 255);
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->string('provinsi', 100)->default('Sulawesi Selatan');
            $table->string('logo_url', 500)->nullable();
            $table->boolean('is_visible')->default(true)->comment('Apakah tampil di portal publik');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
