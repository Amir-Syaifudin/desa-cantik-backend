<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'is_active',
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function geospatial_data()
    {
        return $this -> hasMany(GeospatialData::class);
    }

    public function thematic_maps()
    {
        return $this -> hasMany(ThematicMap::class);
    }

    public function modules()
    {
        return $this -> hasMany(Module::class);
    }
}
