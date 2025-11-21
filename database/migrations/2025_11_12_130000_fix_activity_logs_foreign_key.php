<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'desa_id')) {
                $table->dropForeign(['desa_id']);
                $table->foreign('desa_id')
                    ->references('id')
                    ->on('desa')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'desa_id')) {
                $table->dropForeign(['desa_id']);
                $table->foreign('desa_id')
                    ->references('id')
                    ->on('desa')
                    ->nullOnDelete();
            }
        });
    }
};
