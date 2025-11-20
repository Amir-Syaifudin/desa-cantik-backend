<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray($request): array
    {
        $publishedAt = $this->published_at;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'date' => $publishedAt?->toDateString(),
            'year' => $publishedAt?->year,
            'month' => $publishedAt?->month,
            'description' => $this->description,
            'imageUrl' => 'https://placehold.co/300x400/BFDBFE/1E3A8A?text=' . urlencode($this->title),
            'status' => $this->status,
            'category' => $this->category,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size_bytes,
            'file_type' => $this->file_type,
            'download_url' => $this->download_url,
            'uploaded_by' => $this->whenLoaded('uploader', function () {
                return [
                    'id' => $this->uploader?->id,
                    'full_name' => $this->uploader?->full_name,
                ];
            }),
            'village' => $this->whenLoaded('village', function () {
                return [
                    'id' => $this->village?->id,
                    'name' => $this->village?->nama_desa,
                    'code' => $this->village?->kode_desa,
                ];
            }),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
