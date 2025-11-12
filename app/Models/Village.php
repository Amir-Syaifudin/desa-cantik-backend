<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Village extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'villages';

    protected $fillable = [
        'village_code',
        'name',        
        'kecamatan',
        'kabupaten',
        'provinsi',
        'logo_url',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'village_id');
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(VillageStatistic::class, 'village_id');
    }
}
