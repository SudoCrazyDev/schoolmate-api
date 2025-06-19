<?php

namespace Database\Factories;

use App\Models\Training;
use App\Models\TrainingImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'date' => $this->faker->date(),
            // 'images' attribute removed
        ];
    }

    /**
     * Configure the model factory to add images.
     *
     * @param int $count
     * @return $this
     */
    public function withImages(int $count = 1)
    {
        return $this->afterCreating(function (Training $training) use ($count) {
            TrainingImage::factory()->count($count)->create([
                'training_id' => $training->id,
            ]);
        });
    }
}
