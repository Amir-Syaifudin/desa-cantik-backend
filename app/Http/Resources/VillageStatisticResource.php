<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VillageStatisticResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'village_id' => $this->village_id,
            'statistic_type' => StatisticTypeResource::make($this->whenLoaded('statisticType')),
            'statistic_type_id' => $this->statistic_type_id,
            'indicator_name' => $this->indicator_name,
            'title' => $this->indicator_name, // Frontend alias
            'name' => $this->indicator_name,
            'value' => $this->value !== null ? (float) $this->value : null,
            'unit' => $this->unit,
            'year' => $this->year,
            'period' => $this->period,
            'source' => $this->source,
            'notes' => $this->notes,
            'status' => $this->status ?? 'Menunggu Validasi',
            'fileName' => $this->file_name,
            'fileUrl' => $this->file_url,
            'subject' => $this->statisticType?->category ?? 'Umum', // Frontend alias
            'updatedDate' => $this->updated_at?->toISOString(), // Frontend alias
            'type' => $this->whenLoaded('statisticType', function () {
                return [
                    'id' => $this->statisticType?->id,
                    'name' => $this->statisticType?->name,
                    'category' => $this->statisticType?->category,
                ];
            }),
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator?->id,
                    'full_name' => $this->creator?->full_name ?? $this->creator?->name,
                ];
            }),
            'updated_by' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater?->id,
                    'full_name' => $this->updater?->full_name ?? $this->updater?->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
