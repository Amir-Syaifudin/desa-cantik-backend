<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Models\Village;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PublicationService
{
    public function storeFile(UploadedFile $file, Village $village): array
    {
        $directory = $this->buildDirectory($village->id, (int) now()->year);
        $filename = $this->buildFilename($file);

        try {
            $path = $file->storeAs($directory, $filename, 'public');
        } catch (Throwable $exception) {
            throw new FileUploadException('Gagal menyimpan file publikasi.', 'PUBLICATION_UPLOAD_FAILED', 422);
        }

        return [
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'file_size_bytes' => $file->getSize(),
        ];
    }

    public function deleteFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    protected function buildDirectory(int $villageId, int $year): string
    {
        return "publications/village_{$villageId}/{$year}";
    }

    protected function buildFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        return Str::uuid()->toString().'.'.$extension;
    }
}
