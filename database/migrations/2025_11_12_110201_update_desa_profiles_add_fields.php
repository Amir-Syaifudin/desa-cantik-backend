<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desa_profiles', function (Blueprint $table) {
            $table->decimal('area', 10, 2)->nullable()->after('misi');
            $table->unsignedBigInteger('population')->nullable()->after('area');
            $table->decimal('population_density', 10, 2)->nullable()->after('population');
            $table->string('address', 255)->nullable()->after('population_density');
            $table->string('phone', 50)->nullable()->after('address');
            $table->string('email', 150)->nullable()->after('phone');
            $table->string('website', 150)->nullable()->after('email');
            $table->string('logo_url', 500)->nullable()->after('website');
            $table->boolean('is_featured')->default(false)->after('logo_url');
            $table->string('thumbnail_url', 500)->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('desa_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'area',
                'population',
                'population_density',
                'address',
                'phone',
                'email',
                'website',
                'logo_url',
                'is_featured',
                'thumbnail_url',
            ]);
        });
    }
};
