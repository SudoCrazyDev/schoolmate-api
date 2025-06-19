<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Training; // Added import
use Illuminate\Support\Str; // Added import

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingImage>
 */
class TrainingImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_id' => Training::factory(),
            'path' => 'trainings/' . Str::random(10) . '.jpg',
        ];
    }
}
