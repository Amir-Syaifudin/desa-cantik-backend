<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Village extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    // Update relasi foreign key ke 'desa_id'
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'desa_id');
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(VillageStatistic::class, 'desa_id');
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

    public function profile(): HasOne
    {
        // Update foreign key
        return $this->hasOne(VillageProfile::class, 'desa_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'desa_id');
    }

    public function mapPoints(): HasManyThrough
    {
        return $this->hasManyThrough(
            MapPoint::class,
            ThematicMap::class,
            'desa_id',
            'thematic_map_id',
            'id',
            'id'
        );
    }
}