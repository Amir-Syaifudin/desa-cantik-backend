<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['desa_id']);
        });

        // Rename column using Laravel's schema builder (database-agnostic)
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('desa_id', 'village_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('restrict');

            $table->foreign('village_id')
                ->references('id')
                ->on('villages')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['village_id']);
        });

        // Reverse the column rename
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('village_id', 'desa_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('restrict');

            $table->foreign('desa_id')
                ->references('id')
                ->on('desa')
                ->onDelete('set null');
        });
    }
};
