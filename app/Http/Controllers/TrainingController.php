<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // For generating unique filenames

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['data' => Training::all()]);
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
            'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Max 2MB per image
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
                $path = $imageFile->storeAs('trainings', $filename, 'public');
                $imagePaths[] = $path;
            }
        }

        $validatedData['images'] = json_encode($imagePaths); // Store as JSON array

        $training = Training::create($validatedData);

        return response()->json($training, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        $imagePaths = $training->images ? (is_array($training->images) ? $training->images : json_decode($training->images, true)) : [];

        if ($request->hasFile('images')) {
            // Delete old images if new ones are uploaded
            if (!empty($imagePaths)) {
                foreach ($imagePaths as $oldImagePath) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }

            $newImagePaths = [];
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
                $path = $imageFile->storeAs('trainings', $filename, 'public');
                $newImagePaths[] = $path;
            }
            $validatedData['images'] = json_encode($newImagePaths);
        } else if (array_key_exists('images', $validatedData) && $validatedData['images'] === null) {
            // If images field is explicitly set to null (e.g. to remove all images)
             if (!empty($imagePaths)) {
                foreach ($imagePaths as $oldImagePath) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            $validatedData['images'] = json_encode([]);
        }


        $training->update($validatedData);

        return response()->json($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        // Delete associated images from storage
        $imagePaths = $training->images ? (is_array($training->images) ? $training->images : json_decode($training->images, true)) : [];
        if (!empty($imagePaths)) {
            foreach ($imagePaths as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $training->delete();

        return response()->json(null, 204); // Or a success message with 200
    }
}
