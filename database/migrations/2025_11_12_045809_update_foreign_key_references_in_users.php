<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['desa_id']);
        });
        
        DB::statement('ALTER TABLE users CHANGE desa_id village_id BIGINT UNSIGNED NULL');
        
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
        
        DB::statement('ALTER TABLE users CHANGE village_id desa_id BIGINT UNSIGNED NULL');
        
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('user_roles')
                ->onDelete('restrict');
                
            $table->foreign('desa_id')
                ->references('id')
                ->on('desa')
                ->onDelete('set null');
        });
    }
};