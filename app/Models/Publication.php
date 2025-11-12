<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Publication extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'desa_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size_bytes',
        'published_at',
        'uploaded_by',
        // Legacy columns retained for future reference / backward compatibility
        'category',
        'file_url',
        'file_size',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    protected $appends = [
        'download_url',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'desa_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (! $this->id) {
            return null;
        }

        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return route('publications.download', $this->id);
        }

        return null;
    }
}
