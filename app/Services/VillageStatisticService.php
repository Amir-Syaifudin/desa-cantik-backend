<?php

namespace App\Services;

use App\Exports\VillageStatisticsExport;
use App\Imports\VillageStatisticRowsImport;
use App\Models\StatisticType;
use App\Models\User;
use App\Models\Village;
use App\Models\VillageStatistic;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class VillageStatisticService
{
    public function import(Village $village, UploadedFile $file, User $user): array
    {
        // Validate file before processing
        $this->validateImportFile($file);

        $import = new VillageStatisticRowsImport();

        try {
            Excel::import($import, $file);
        } catch (Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'file' => 'File tidak dapat diproses. Pastikan format file sesuai dengan template.',
            ]);
        }

        /** @var Collection<int, array<string, mixed>> $rows */
        $rows = $import->rows ?? collect();

        // Check if file has data
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'File tidak memiliki data. Pastikan file berisi data statistik.',
            ]);
        }

        // Check row limit to prevent memory issues
        $maxRows = config('excel.imports.max_rows', 5000);
        if ($rows->count() > $maxRows) {
            throw ValidationException::withMessages([
                'file' => "File terlalu besar. Maksimal {$maxRows} baris data.",
            ]);
        }

        $summary = [
            'total_rows' => $rows->count(),
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // account for heading row
            $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;
            $normalized = $this->normalizeRow($rowData);

            if ($normalized instanceof ValidationException) {
                $summary['failed']++;
                $summary['errors'][] = [
                    'row' => $rowNumber,
                    'error' => $normalized->getMessage(),
                ];
                continue;
            }

            try {
                DB::transaction(function () use ($village, $normalized, $user) {
                    VillageStatistic::create([
                        'village_id' => $village->id,
                        'statistic_type_id' => $normalized['statistic_type_id'],
                        'indicator_name' => $normalized['indicator_name'],
                        'value' => $normalized['value'],
                        'unit' => $normalized['unit'],
                        'year' => $normalized['year'],
                        'period' => $normalized['period'],
                        'source' => $normalized['source'],
                        'notes' => $normalized['notes'],
                        'created_by' => $user->id,
                    ]);
                });

                $summary['imported']++;
            } catch (Throwable $e) {
                report($e);
                $summary['failed']++;
                $summary['errors'][] = [
                    'row' => $rowNumber,
                    'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
                ];
            }
        }

        return $summary;
    }

    /**
     * Validate import file before processing
     */
    protected function validateImportFile(UploadedFile $file): void
    {
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes

        if ($file->getSize() > $maxSize) {
            throw ValidationException::withMessages([
                'file' => 'Ukuran file terlalu besar. Maksimal 10MB.',
            ]);
        }

        $allowedMimes = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $allowedExtensions = ['csv', 'xlsx', 'xls'];

        if (!in_array($file->getMimeType(), $allowedMimes) && !in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak didukung. Gunakan CSV atau Excel (xlsx/xls).',
            ]);
        }
    }

    public function export(Village $village, string $format, ?int $year = null): BinaryFileResponse
    {
        $format = strtolower($format);
        $allowedFormats = [
            'csv' => ExcelWriter::CSV,
            'xlsx' => ExcelWriter::XLSX,
        ];

        if (! isset($allowedFormats[$format])) {
            throw ValidationException::withMessages([
                'format' => 'Format tidak didukung. Gunakan csv atau xlsx.',
            ]);
        }

        $statistics = $village->statistics()
            ->with('statisticType')
            ->when($year, fn($query) => $query->where('year', $year))
            ->orderBy('year')
            ->orderBy('indicator_name')
            ->get();

        $filename = sprintf(
            'statistik_desa_%s_%s.%s',
            Str::slug($village->nama_desa ?? $village->id, '_'),
            $year ?? 'semua',
            $format
        );

        return Excel::download(
            new VillageStatisticsExport($statistics),
            $filename,
            $allowedFormats[$format]
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|ValidationException
     */
    protected function normalizeRow(array $row): array|ValidationException
    {
        $code = strtoupper(trim((string) ($row['statistic_type_code'] ?? $row['code'] ?? '')));

        if ($code === '') {
            return ValidationException::withMessages([
                'statistic_type_code' => 'Kolom statistic_type_code wajib diisi',
            ]);
        }

        $statType = StatisticType::where('code', $code)->first();

        if (! $statType) {
            return ValidationException::withMessages([
                'statistic_type_code' => "Statistic type code not found: {$code}",
            ]);
        }

        $indicatorName = trim((string) ($row['indicator_name'] ?? ''));
        $value = $row['value'] ?? null;
        $year = (int) ($row['year'] ?? 0);

        if ($indicatorName === '') {
            return ValidationException::withMessages([
                'indicator_name' => 'Indicator name wajib diisi',
            ]);
        }

        if (! is_numeric($value)) {
            return ValidationException::withMessages([
                'value' => 'Value must be numeric',
            ]);
        }

        if ($year < 2000 || $year > (int) date('Y') + 1) {
            return ValidationException::withMessages([
                'year' => 'Year tidak valid',
            ]);
        }

        return [
            'statistic_type_id' => $statType->id,
            'indicator_name' => $indicatorName,
            'value' => (float) $value,
            'unit' => $this->sanitizeOptional(isset($row['unit']) ? (string) $row['unit'] : null, 50),
            'year' => $year,
            'period' => $this->sanitizeOptional(isset($row['period']) ? (string) $row['period'] : null, 50),
            'source' => $this->sanitizeOptional(isset($row['source']) ? (string) $row['source'] : null, 255),
            'notes' => $this->sanitizeOptional(isset($row['notes']) ? (string) $row['notes'] : null),
        ];
    }

    protected function sanitizeOptional(?string $value, ?int $maxLength = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        if ($maxLength !== null) {
            return mb_substr($trimmed, 0, $maxLength);
        }

        return $trimmed;
    }
}
