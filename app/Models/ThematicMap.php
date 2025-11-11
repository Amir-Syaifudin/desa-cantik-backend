<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThematicMap extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan di database
    protected $table = 'thematic_maps';

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'village_id',    // ID desa yang terkait dengan peta tematik
        'name',          // Nama peta tematik
        'points',        // Titik-titik (coordinates) atau data yang terkait
        'geojson_data',  // Data GeoJSON yang disimpan
    ];

    // Relasi dengan tabel Village (relasi banyak ke satu)
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
