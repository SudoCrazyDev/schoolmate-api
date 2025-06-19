<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Training;
use App\Models\TrainingImage; // Added import
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TrainingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_trainings(): void
    {
        $training1 = Training::factory()->withImages(2)->create();
        $training2 = Training::factory()->withImages(1)->create();

        $response = $this->getJson('/api/trainings');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $training1->id)
            ->assertJsonPath('data.0.images.0.path', $training1->images->first()->path)
            ->assertJsonPath('data.1.id', $training2->id)
            ->assertJsonPath('data.1.images.0.path', $training2->images->first()->path);
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
            ->assertJsonFragment([
                'title' => $trainingData['title'],
                'description' => $trainingData['description'],
                'images' => [], // Expect empty images array
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
            ->assertJsonFragment(['title' => $trainingData['title']])
            ->assertJsonCount(2, 'images'); // Assert 2 image objects in response

        $this->assertDatabaseHas('trainings', ['title' => $trainingData['title']]);
        $createdTraining = Training::where('title', $trainingData['title'])->first();
        $this->assertCount(2, $createdTraining->images);

        foreach ($createdTraining->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_validation_fails_for_create_training_with_invalid_data(): void
    {
        $trainingData = [
            'description' => 'This is a test training description.',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/trainings', $trainingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Test invalid image file
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $response = $this->postJson('/api/trainings', [
            'title' => 'Title with invalid image',
            'description' => 'Valid desc',
            'date' => now()->format('Y-m-d'),
            'images' => [$invalidFile]
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['images.0']);
    }

    public function test_can_get_single_training(): void
    {
        $training = Training::factory()->withImages(1)->create();

        $response = $this->getJson("/api/trainings/{$training->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $training->id)
            ->assertJsonPath('data.title', $training->title)
            ->assertJsonCount(1, 'data.images')
            ->assertJsonPath('data.images.0.path', $training->images->first()->path);
    }

    public function test_can_update_training_without_changing_images(): void
    {
        Storage::fake('public');
        $training = Training::factory()->create();

        $imageFile1 = UploadedFile::fake()->image('initial1.jpg');
        $path1 = $imageFile1->store('trainings', 'public');
        $training->images()->create(['path' => $path1]);

        $imageFile2 = UploadedFile::fake()->image('initial2.jpg');
        $path2 = $imageFile2->store('trainings', 'public');
        $training->images()->create(['path' => $path2]);

        $originalImagePaths = $training->images->pluck('path')->toArray();

        $updateData = [
            'title' => 'Updated Training Title',
            'description' => 'Updated description.',
        ];

        $response = $this->putJson("/api/trainings/{$training->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $updateData['title']]);

        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'title' => $updateData['title'],
        ]);

        $updatedTraining = Training::find($training->id);
        $this->assertCount(2, $updatedTraining->images); // Ensure still 2 images
        foreach ($originalImagePaths as $path) {
            $this->assertContains($path, $updatedTraining->images->pluck('path'));
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_can_update_training_replacing_all_images(): void
    {
        Storage::fake('public');
        // Create initial training with images that will be stored by Storage::fake
        $training = Training::factory()->create();
        $oldImageFile1 = UploadedFile::fake()->image('old1.jpg');
        $oldPath1 = $oldImageFile1->store('trainings', 'public');
        $training->images()->create(['path' => $oldPath1]);

        $oldImageFile2 = UploadedFile::fake()->image('old2.jpg');
        $oldPath2 = $oldImageFile2->store('trainings', 'public');
        $training->images()->create(['path' => $oldPath2]);

        $newImageFile = UploadedFile::fake()->image('new.jpg');
        $updateData = [
            'title' => 'Updated Title with New Images',
            'images' => [$newImageFile],
        ];

        $response = $this->putJson("/api/trainings/{$training->id}", $updateData);
        $response->assertStatus(200);

        $this->assertDatabaseHas('trainings', ['id' => $training->id, 'title' => $updateData['title']]);

        $updatedTraining = Training::find($training->id);
        $this->assertCount(1, $updatedTraining->images);
        $newImagePath = $updatedTraining->images->first()->path;

        Storage::disk('public')->assertMissing($oldPath1);
        Storage::disk('public')->assertMissing($oldPath2);
        Storage::disk('public')->assertExists($newImagePath);
    }

    public function test_can_update_training_removing_all_images(): void
    {
        Storage::fake('public');
        $training = Training::factory()->create();
        $oldImageFile = UploadedFile::fake()->image('old_remove.jpg');
        $oldPath = $oldImageFile->store('trainings', 'public');
        $training->images()->create(['path' => $oldPath]);

        $updateData = [
            'title' => 'Title with images removed',
            'images' => [], // Send empty array to remove images
        ];

        $response = $this->putJson("/api/trainings/{$training->id}", $updateData);
        $response->assertStatus(200)
                 ->assertJsonFragment(['images' => []]);

        $this->assertDatabaseHas('trainings', ['id' => $training->id, 'title' => $updateData['title']]);
        $updatedTraining = Training::find($training->id);
        $this->assertCount(0, $updatedTraining->images);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_can_delete_training(): void
    {
        Storage::fake('public');
        $training = Training::factory()->create();
        $imageFile = UploadedFile::fake()->image('test_delete.jpg');
        $path = $imageFile->store('trainings', 'public');
        $trainingImage = $training->images()->create(['path' => $path]);


        $this->assertDatabaseHas('trainings', ['id' => $training->id]);
        $this->assertDatabaseHas('training_images', ['id' => $trainingImage->id]);
        Storage::disk('public')->assertExists($path);

        $response = $this->deleteJson("/api/trainings/{$training->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('trainings', ['id' => $training->id]);
        $this->assertDatabaseMissing('training_images', ['id' => $trainingImage->id]); // Cascade should handle this
        Storage::disk('public')->assertMissing($path);
    }
}
