<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'thematic_map_id',
        'name',
        'description',
        'category',
        'latitude',
        'longitude',
        'icon_url',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'metadata' => 'array',
    ];

    public function thematicMap(): BelongsTo
    {
        return $this->belongsTo(ThematicMap::class);
    }
}
