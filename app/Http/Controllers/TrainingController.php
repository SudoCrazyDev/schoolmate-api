<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingImage; // Added import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['data' => Training::with('images')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $dataToCreate = [
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'date' => $validatedData['date'],
            'user_id' => $request->user()->id, // Associate authenticated user
        ];

        $training = Training::create($dataToCreate);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
                $path = $imageFile->storeAs('trainings', $filename, 'public');
                $training->images()->create(['path' => $path]);
            }
        }

        $training->load('images'); // Load the images relationship for the response
        return response()->json($training, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        $training->load('images');
        return response()->json(['data' => $training]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Training $training)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'images' => 'nullable|array', // Can be an empty array to remove all images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Update non-image fields
        $training->update([
            'title' => $validatedData['title'] ?? $training->title,
            'description' => $validatedData['description'] ?? $training->description,
            'date' => $validatedData['date'] ?? $training->date,
        ]);

        if ($request->hasFile('images')) {
            // Delete old images (files and records)
            foreach ($training->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete(); // Delete the TrainingImage record
            }

            // Store new images
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
                $path = $imageFile->storeAs('trainings', $filename, 'public');
                $training->images()->create(['path' => $path]);
            }
        } elseif (array_key_exists('images', $validatedData) && ($validatedData['images'] === null || (is_array($validatedData['images']) && empty($validatedData['images'])) ) ) {
            // If 'images' key is present and null or an empty array, remove all existing images
            foreach ($training->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }


        $training->load('images'); // Load the images relationship for the response
        return response()->json($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        // Delete associated image files
        // TrainingImage records will be deleted by onDelete('cascade')
        foreach ($training->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $training->delete();

        return response()->json(null, 204);
    }
}
