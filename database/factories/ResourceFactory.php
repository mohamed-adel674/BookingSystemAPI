<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Service',
            'description' => $this->faker->sentence,
            'type' => 'service', // Changed from 'room' to 'service'
            'capacity' => $this->faker->numberBetween(5, 50),
            'is_active' => true,
        ];
    }
}
