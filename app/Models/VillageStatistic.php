<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillageStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'desa_id',
        'statistic_type_id',
        'indicator_name',
        'value',
        'unit',
        'year',
        'period',
        'source',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'year' => 'integer',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'desa_id');
    }

    public function statisticType(): BelongsTo
    {
        return $this->belongsTo(StatisticType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
