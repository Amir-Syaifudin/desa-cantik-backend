<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenApi\Generator;
use Illuminate\Support\Facades\File;

class GenerateOpenApi extends Command
{
    protected $signature = 'openapi:generate {--yaml : Save a YAML copy}';
    protected $description = 'Generate OpenAPI documentation JSON (and YAML optionally)';

    public function handle()
    {
        $this->info('Scanning for OpenAPI attributes...');

        // Use config paths from l5-swagger if present, otherwise default
        $annotations = config('l5-swagger.documentations.default.paths.annotations', [base_path('app/Http/Controllers')]);

        $analysis = Generator::scan($annotations);

        $json = $analysis->toJson();

        $docsPath = storage_path('api-docs');
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }

        File::put($docsPath . '/api-docs.json', $json);
        $this->info('Generated: ' . $docsPath . '/api-docs.json');

        if ($this->option('yaml')) {
            try {
                $yaml = $analysis->toYaml();
                File::put($docsPath . '/api-docs.yaml', $yaml);
                $this->info('Generated: ' . $docsPath . '/api-docs.yaml');
            } catch (\Throwable $e) {
                $this->error('Failed to generate YAML: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
