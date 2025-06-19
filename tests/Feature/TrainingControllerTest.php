<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Training;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TrainingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_trainings(): void
    {
        Training::factory()->count(3)->create();

        $response = $this->getJson('/api/trainings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_training_without_images(): void
    {
        $trainingData = [
            'title' => 'New Training Title',
            'description' => 'This is a test training description.',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/trainings', $trainingData);

        $response->assertStatus(201)
            ->assertJsonFragment([ // Check for a fragment of the data
                'title' => $trainingData['title'],
                'description' => $trainingData['description'],
            ]);

        $this->assertDatabaseHas('trainings', [
            'title' => $trainingData['title']
        ]);
    }

    public function test_can_create_training_with_images(): void
    {
        Storage::fake('public');

        $image1 = UploadedFile::fake()->image('training1.jpg');
        $image2 = UploadedFile::fake()->image('training2.png');

        $trainingData = [
            'title' => 'Training With Images',
            'description' => 'Description for training with images.',
            'date' => now()->format('Y-m-d'),
            'images' => [$image1, $image2],
        ];

        $response = $this->postJson('/api/trainings', $trainingData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $trainingData['title']]);

        $this->assertDatabaseHas('trainings', ['title' => $trainingData['title']]);

        $createdTraining = Training::where('title', $trainingData['title'])->first();
        $this->assertNotNull($createdTraining->images);
        $imagePaths = json_decode($createdTraining->images, true);
        $this->assertCount(2, $imagePaths);

        foreach ($imagePaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_validation_fails_for_create_training_with_invalid_data(): void
    {
        $trainingData = [
            // Title is missing
            'description' => 'This is a test training description.',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/trainings', $trainingData);

        $response->assertStatus(422) // Unprocessable Entity
            ->assertJsonValidationErrors(['title']);
    }

    public function test_can_get_single_training(): void
    {
        $training = Training::factory()->create();

        $response = $this->getJson("/api/trainings/{$training->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $training->id,
                'title' => $training->title
            ]);
    }

    public function test_can_update_training_without_changing_images(): void
    {
        Storage::fake('public');
        $image1 = UploadedFile::fake()->image('initial.jpg');
        $initialImagePath = $image1->store('trainings', 'public');

        $training = Training::factory()->create([
            'images' => json_encode([$initialImagePath])
        ]);

        $updateData = [
            'title' => 'Updated Training Title',
            'description' => 'Updated description.',
            // Not sending 'images' key, or sending it as null if that's how the API is designed
            // For this test, we assume not sending 'images' means "do not change images"
        ];

        $response = $this->putJson("/api/trainings/{$training->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $updateData['title']]);

        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'title' => $updateData['title'],
        ]);

        $updatedTraining = Training::find($training->id);
        $this->assertNotNull($updatedTraining->images);
        $imagePaths = json_decode($updatedTraining->images, true);
        $this->assertCount(1, $imagePaths);
        $this->assertEquals($initialImagePath, $imagePaths[0]);
        Storage::disk('public')->assertExists($initialImagePath);
    }

    public function test_can_update_training_with_new_images(): void
    {
        Storage::fake('public');
        $oldImage = UploadedFile::fake()->image('old.jpg');
        $oldImagePath = $oldImage->store('trainings', 'public');

        $training = Training::factory()->create([
            'images' => json_encode([$oldImagePath])
        ]);

        $newImage1 = UploadedFile::fake()->image('new1.jpg');
        $newImage2 = UploadedFile::fake()->image('new2.png');

        $updateData = [
            'title' => 'Updated Title with New Images',
            'images' => [$newImage1, $newImage2],
        ];

        $response = $this->putJson("/api/trainings/{$training->id}", $updateData);
        $response->assertStatus(200);

        $this->assertDatabaseHas('trainings', ['id' => $training->id, 'title' => $updateData['title']]);

        $updatedTraining = Training::find($training->id);
        $this->assertNotNull($updatedTraining->images);
        $newImagePaths = json_decode($updatedTraining->images, true);
        $this->assertCount(2, $newImagePaths);

        Storage::disk('public')->assertMissing($oldImagePath);
        foreach ($newImagePaths as $path) {
            Storage::disk('public')->assertExists($path);
            $this->assertStringContainsString('trainings/', $path); // Ensure they are in the 'trainings' directory
        }
    }

    public function test_can_delete_training(): void
    {
        Storage::fake('public');
        $image1 = UploadedFile::fake()->image('training_to_delete.jpg');
        $imagePath = $image1->store('trainings', 'public');

        $training = Training::factory()->create([
            'images' => json_encode([$imagePath])
        ]);

        $this->assertDatabaseHas('trainings', ['id' => $training->id]);
        Storage::disk('public')->assertExists($imagePath);

        $response = $this->deleteJson("/api/trainings/{$training->id}");

        $response->assertStatus(204); // No Content

        $this->assertDatabaseMissing('trainings', ['id' => $training->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }
}
