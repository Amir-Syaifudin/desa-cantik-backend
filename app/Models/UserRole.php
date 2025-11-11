<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    public const BPS_ADMIN = 'bps_admin';
    public const VILLAGE_OFFICER = 'village_officer';
    public const GUEST = 'guest';

    protected $fillable = [
        'role_name',
        'display_name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
