<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasFactory;

    protected $table = 'desa';

    protected $fillable = [
        'kode_desa',
        'nama_desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'logo_url',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function statistics(): HasMany
    {
        return $this->hasMany(VillageStatistic::class, 'village_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'desa_id');
    }

    public function geospatialData(): HasMany
    {
        return $this->hasMany(GeospatialData::class, 'desa_id');
    }

    public function thematicMaps(): HasMany
    {
        return $this->hasMany(ThematicMap::class, 'desa_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'desa_id');
    }
}
