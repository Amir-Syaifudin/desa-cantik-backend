<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        return [
            'desa_id' => Village::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'file_path' => 'publications/'.fake()->lexify('file_????').'.pdf',
            'file_name' => fake()->lexify('document_????').'.pdf',
            'file_type' => 'pdf',
            'file_size_bytes' => fake()->numberBetween(10_000, 1_000_000),
            'published_at' => fake()->date(),
            'uploaded_by' => User::factory(),
            'file_url' => fake()->url(),
        ];
    }
}
