<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Village>
 */
class VillageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'village_code' => fake()->unique()->numerify('7316#######'),
            'name' => fake()->city(),
            'kecamatan' => fake()->citySuffix(),
            'kabupaten' => 'Toraja Utara',
            'provinsi' => 'Sulawesi Selatan',
            'logo_url' => null,
            'is_visible' => true,
        ];
    }
}
