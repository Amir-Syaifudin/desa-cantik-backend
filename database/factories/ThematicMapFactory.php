<?php

namespace Database\Factories;

use App\Models\ThematicMap;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThematicMap>
 */
class ThematicMapFactory extends Factory
{
    protected $model = ThematicMap::class;

    public function definition(): array
    {
        return [
            'desa_id' => Village::factory(),
            'map_name' => fake()->words(3, true),
            'map_type' => fake()->randomElement(['Ekonomi', 'Pendidikan', 'Kesehatan']),
            'description' => fake()->sentence(),
            'layer_config' => ['color' => fake()->safeHexColor()],
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
