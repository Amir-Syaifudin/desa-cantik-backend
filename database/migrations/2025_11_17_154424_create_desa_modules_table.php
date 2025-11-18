<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('desa_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('village_id')->index();
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();

            $table->unique(['village_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desa_modules');
    }
};
