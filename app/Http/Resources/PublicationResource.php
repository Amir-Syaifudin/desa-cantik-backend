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
            'description' => $this->description,
            'status' => $this->status,
            'category' => $this->category,
            'publishedAt' => $publishedAt?->toDateString(),
            'date' => $publishedAt?->toDateString(),
            'year' => $publishedAt?->year,
            'month' => $publishedAt?->month,
            'fileName' => $this->file_name,
            'fileSize' => $this->file_size_bytes,
            'fileType' => $this->file_type,
            'fileUrl' => $this->file_url ?? $this->download_url,
            'downloadUrl' => $this->download_url,
            'imageUrl' => $this->cover_url
                ?? 'https://placehold.co/300x400/BFDBFE/1E3A8A?text=' . urlencode($this->title),
            'uploaded_by' => $this->whenLoaded('uploader', function () {
                return [
                    'id' => $this->uploader?->id,
                    'full_name' => $this->uploader?->full_name,
                ];
            }),
            'village' => $this->whenLoaded('village', function () {
                return [
                    'id' => $this->village?->id,
                    'name' => $this->village?->name ?? $this->village?->nama_desa,
                    'code' => $this->village?->village_code ?? $this->village?->kode_desa,
                ];
            }),
            'villageName' => $this->village?->name,
            'created_at' => optional($this->created_at)->toISOString(),
            'createdAt' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'updatedAt' => optional($this->updated_at)->toISOString(),
        ];
    }
}
