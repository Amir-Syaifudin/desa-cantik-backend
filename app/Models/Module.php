<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'desa_modules';

    protected $fillable = ['name', 'status', 'village_id'];

    // Menyatakan bahwa modul milik desa
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
