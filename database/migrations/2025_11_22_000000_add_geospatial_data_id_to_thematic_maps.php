<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thematic_maps', function (Blueprint $table) {
            if (!Schema::hasColumn('thematic_maps', 'geospatial_data_id')) {
                $table->foreignId('geospatial_data_id')
                      ->nullable()
                      ->after('description')
                      ->constrained('geospatial_data')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('thematic_maps', function (Blueprint $table) {
            if (Schema::hasColumn('thematic_maps', 'geospatial_data_id')) {
                $table->dropForeign(['geospatial_data_id']);
                $table->dropColumn('geospatial_data_id');
            }
        });
    }
};