<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    use HasFactory;

    protected $table = 'desa_modules';

    // SESUAIKAN DENGAN MIGRATION
    protected $fillable = [
        'desa_id',      // Sebelumnya: village_id
        'module_name',  // Sebelumnya: name
        'is_active',    // Sebelumnya: status
        'activated_at',
        'deactivated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    // Update relasi ke 'desa'
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'desa_id');
    }
}