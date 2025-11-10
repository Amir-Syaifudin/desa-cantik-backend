<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add custom fields after 'email'
            $table->string('username', 100)->unique()->nullable()->after('id');
            $table->string('full_name', 150)->after('email');
            $table->string('phone_number', 20)->nullable()->after('full_name');
            $table->foreignId('role_id')->after('phone_number')->constrained('roles')->onDelete('restrict');
            $table->foreignId('desa_id')->nullable()->after('role_id')->constrained('desa')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('desa_id');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['desa_id']);
            $table->dropColumn([
                'username',
                'full_name',
                'phone_number',
                'role_id',
                'desa_id',
                'is_active',
                'deleted_at'
            ]);
        });
    }
};
