<?php
<<<<<<< HEAD
=======

>>>>>>> 94385f3cdb1d2dcc1ae7fe7b9eb0ba34133a217f
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Relations\HasMany;
>>>>>>> 94385f3cdb1d2dcc1ae7fe7b9eb0ba34133a217f

class Village extends Model
{
    use HasFactory;

<<<<<<< HEAD
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
=======
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
>>>>>>> 94385f3cdb1d2dcc1ae7fe7b9eb0ba34133a217f
    }
}
