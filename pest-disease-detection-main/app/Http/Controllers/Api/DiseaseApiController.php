<?php

namespace App\Http\Controllers\Api;

use App\Models\Disease;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DiseaseApiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->is_admin == 1) {
            $diseases = Disease::all();
        } else {
            $diseases = Disease::where('user_id', $user->id)->get();
        }
        return response()->json([
            'success' => true,
            'message' => 'All disease fetch successfully!',
            'data' =>  $diseases,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'nullable|image|mimes:png,jpg,jpeg,svg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasfile('file')) {
            $imagePath = $request->file('file')->store('plant_disease_images', 'public');
        }

        return response()->json([
            'success' => true,
            'file_path' => $imagePath,
            'url' => asset('storage/' . $imagePath),

        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'disease_name' => 'required|string|max:255',
            'disease_description' => 'required|string',
            'remedies' => 'required|string',
            'weather' => 'required|string|max:255',
            'image' => 'nullable|string|max:255' // Image validation
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create Disease entry
        $disease = Disease::create([
            'user_id' => auth()->id(), // use logged in user
            'disease_name' => $request->disease_name,
            'disease_description' => $request->disease_description,
            'remedies' => $request->remedies,
            'weather' => $request->weather,
            'image' => $request->image,
        ]);

        return response()->json([
            'message' => 'Disease created successfully',
            'data' => $disease
        ], 201);
    }

    public function edit($id)
    {
        $disease = Disease::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $disease,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'disease_name' => 'required|string|max:255',
            'disease_description' => 'required|string',
            'remedies' => 'required|string',
            'weather' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Image validation
        ]);

        $disease = Disease::findOrFail($id);
        $disease->update([
            'disease_name' => $request->disease_name,
            'disease_description' => $request->disease_description,
            'remedies' => $request->remedies,
            'weather' => $request->weather,
            'image' => $request->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plant disease updated successfully!',
            'data' => $disease,
        ]);
    }

    public function destroy($id)
    {
        $disease = Disease::findOrFail($id);
        if ($disease) {
            $disease->delete();
        }
        return response()->json([
            'message' =>  'Disease deleted successfully!',
            'data' =>  ['id' => $id],
        ], 200);
    }
}
