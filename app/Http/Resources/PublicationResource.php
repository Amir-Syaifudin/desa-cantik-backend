<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'village_id' => $this->desa_id,
            'title' => $this->title,
            'description' => $this->description,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size_bytes,
            'file_type' => $this->file_type,
            'published_at' => optional($this->published_at)->toDateString(),
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
