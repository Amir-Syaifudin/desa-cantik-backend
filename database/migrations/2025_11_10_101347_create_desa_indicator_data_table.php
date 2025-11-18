<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa_indicator_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained('indicators')->onDelete('cascade');
            $table->year('year');
            $table->decimal('value', 20, 4);
            $table->text('notes')->nullable();
            $table->string('source', 255)->nullable()->comment('Sumber data');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['desa_id', 'indicator_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa_indicator_data');
    }
};
