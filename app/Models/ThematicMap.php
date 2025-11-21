<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThematicMap extends Model
{
    use HasFactory;

    protected $table = 'thematic_maps';

    protected $fillable = [
        'village_id',
        'map_name',
        'map_type',
        'description',
        'geospatial_data_id',
        'layer_config',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'layer_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function geospatialData(): BelongsTo
    {
        return $this->belongsTo(GeospatialData::class, 'geospatial_data_id');
    }
    
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function mapPoints(): HasMany
    {
        return $this->hasMany(MapPoint::class);
    }
}
