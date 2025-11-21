<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa_profiles', function (Blueprint $table) {
            $table->id();
            // Standardisasi menggunakan 'desa_id' dan tabel 'desa'
            $table->foreignId('desa_id')
                  ->unique()
                  ->constrained('desa')
                  ->onDelete('cascade');
                  
            $table->text('deskripsi')->nullable();
            $table->text('sejarah')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            
            // --- Kolom Tambahan (Gabungan dari file yang dihapus) ---
            $table->decimal('area', 10, 2)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->unsignedInteger('households')->nullable(); // Jumlah KK
            $table->unsignedInteger('male_population')->nullable();
            $table->unsignedInteger('female_population')->nullable();
            $table->decimal('population_density', 10, 2)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('foto_url', 500)->nullable();
            // --------------------------------------------------------

            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa_profiles');
    }
};