<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class StatisticType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Clear cache on create, update, or delete
        static::created(function () {
            self::clearCache();
        });

        static::updated(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    public static function clearCache(): void
    {
        // Clear all statistic type caches
        Cache::forget('statistic_types_all');

        // Clear category-specific caches
        // Note: In production, you might want to track which categories exist
        // For now, we clear the main cache
        Cache::flush(); // Use with caution in production with shared cache
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(VillageStatistic::class);
    }
}
