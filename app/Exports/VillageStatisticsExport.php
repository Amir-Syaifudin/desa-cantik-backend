<?php

namespace App\Exports;

use App\Models\VillageStatistic;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VillageStatisticsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param Collection<int, VillageStatistic> $statistics
     */
    public function __construct(
        protected Collection $statistics,
    ) {
    }

    public function collection(): Collection
    {
        return $this->statistics;
    }

    public function headings(): array
    {
        return [
            'statistic_type_code',
            'indicator_name',
            'value',
            'unit',
            'year',
            'period',
            'source',
            'notes',
        ];
    }

    /**
     * @param VillageStatistic $statistic
     */
    public function map($statistic): array
    {
        return [
            $statistic->statisticType?->code,
            $statistic->indicator_name,
            $statistic->value,
            $statistic->unit,
            $statistic->year,
            $statistic->period,
            $statistic->source,
            $statistic->notes,
        ];
    }
}
