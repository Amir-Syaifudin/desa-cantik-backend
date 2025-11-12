<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'roles';

    /**
     * Role constants
     */
    public const BPS_ADMIN = 'bps_admin';
    public const VILLAGE_OFFICER = 'village_officer';
    public const GUEST = 'guest';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_name',
        'display_name',
        'description',
    ];

    /**
     * Get users for this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}