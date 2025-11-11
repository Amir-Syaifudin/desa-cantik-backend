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

        static::updated(function ($model) {
            self::clearCache($model);
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    public static function clearCache($model = null): void
    {
        // Clear all statistic type caches
        Cache::forget('statistic_types_all');

        // Clear category-specific caches
        if ($model && $model->category) {
            Cache::forget("statistic_types_{$model->category}");
        }

        // If we don't have a specific model, clear all known categories
        if (!$model) {
            $categories = ['kependudukan', 'ekonomi', 'kesehatan', 'pendidikan', 'sosial', 'infrastruktur'];
            foreach ($categories as $category) {
                Cache::forget("statistic_types_{$category}");
            }
        }
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(VillageStatistic::class);
    }
}
