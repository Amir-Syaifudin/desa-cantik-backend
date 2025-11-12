<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('desa_profiles')) {
            Schema::table('desa_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('desa_profiles', 'desa_id')) {
                    $table->dropForeign(['desa_id']);
                }
            });

            Schema::table('desa_profiles', function (Blueprint $table) {
                $table->renameColumn('desa_id', 'village_id');
            });

            Schema::table('desa_profiles', function (Blueprint $table) {
                $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            });

            Schema::rename('desa_profiles', 'village_profiles');
        }

        if (Schema::hasTable('desa_modules')) {
            Schema::table('desa_modules', function (Blueprint $table) {
                if (Schema::hasColumn('desa_modules', 'desa_id')) {
                    $table->dropForeign(['desa_id']);
                }
            });

            Schema::table('desa_modules', function (Blueprint $table) {
                $table->renameColumn('desa_id', 'village_id');
            });

            Schema::table('desa_modules', function (Blueprint $table) {
                $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            });

            Schema::rename('desa_modules', 'village_modules');
        }

        if (Schema::hasTable('desa_indicator_data')) {
            Schema::table('desa_indicator_data', function (Blueprint $table) {
                if (Schema::hasColumn('desa_indicator_data', 'desa_id')) {
                    $table->dropForeign(['desa_id']);
                }
            });

            Schema::table('desa_indicator_data', function (Blueprint $table) {
                $table->renameColumn('desa_id', 'village_id');
            });

            Schema::table('desa_indicator_data', function (Blueprint $table) {
                $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            });

            Schema::rename('desa_indicator_data', 'village_indicator_data');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('village_profiles')) {
            Schema::rename('village_profiles', 'desa_profiles');
            Schema::table('desa_profiles', function (Blueprint $table) {
                $table->dropForeign(['village_id']);
            });
            Schema::table('desa_profiles', function (Blueprint $table) {
                $table->renameColumn('village_id', 'desa_id');
            });
            Schema::table('desa_profiles', function (Blueprint $table) {
                $table->foreign('desa_id')->references('id')->on('desa')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('village_modules')) {
            Schema::rename('village_modules', 'desa_modules');
            Schema::table('desa_modules', function (Blueprint $table) {
                $table->dropForeign(['village_id']);
            });
            Schema::table('desa_modules', function (Blueprint $table) {
                $table->renameColumn('village_id', 'desa_id');
            });
            Schema::table('desa_modules', function (Blueprint $table) {
                $table->foreign('desa_id')->references('id')->on('desa')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('village_indicator_data')) {
            Schema::rename('village_indicator_data', 'desa_indicator_data');
            Schema::table('desa_indicator_data', function (Blueprint $table) {
                $table->dropForeign(['village_id']);
            });
            Schema::table('desa_indicator_data', function (Blueprint $table) {
                $table->renameColumn('village_id', 'desa_id');
            });
            Schema::table('desa_indicator_data', function (Blueprint $table) {
                $table->foreign('desa_id')->references('id')->on('desa')->onDelete('cascade');
            });
        }
    }
};
