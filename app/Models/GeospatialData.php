<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeospatialData extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'geospatial_data';

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'village_id',
        'geometry_type',
        'geojson_data',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'geojson_data' => 'array',
        'properties' => 'array',
    ];

    // Relasi dengan tabel Village (relasi banyak ke satu)
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }
}
