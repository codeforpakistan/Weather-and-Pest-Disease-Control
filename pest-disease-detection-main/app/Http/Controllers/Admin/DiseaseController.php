<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiseaseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->is_admin == 1) {
            $diseases = Disease::all();
        } else {
            $diseases = Disease::where('user_id', $user->id)->get();
        }
        return view('admin.plant_diseases.index', compact('diseases'));
    }

    public function create()
    {
        return view('admin.plant_diseases.create');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'nullable|image|mimes:png,jpg,jpeg,svg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('file')) {
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
            return back()->with('error', 'You must be logged in to create a plant disease.');
        }
        $request->validate([
            'disease_name' => 'required|string|max:255',
            'disease_description' => 'required|string',
            'remedies' => 'required|string',
            'weather' => 'required|string|max:255',
            'image' => 'nullable|string|max:255', // Image validation
        ]);



        $diseases = Disease::create([
            'user_id' => auth()->id(), // use logged in user
            'disease_name' => $request->disease_name,
            'disease_description' => $request->disease_description,
            'remedies' => $request->remedies,
            'weather' => $request->weather,
            'image' =>  $request->image // Image store
        ]);

        return redirect()->route('plant_diseases.index')->with('success', 'Plant disease created successfully!');
    }


    public function edit($id)
    {
        $disease = Disease::findOrFail($id);
        return view('admin.plant_diseases.edit', compact('disease'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'disease_name' => 'required|string|max:255',
            'disease_description' => 'required|string',
            'remedies' => 'required|string',
            'weather' => 'required|string|max:255',
            'image' => 'nullable|string|max:255', // image validation
        ]);

        $disease = Disease::findOrFail($id);
        $disease->disease_name = $request->disease_name;
        $disease->disease_description = $request->disease_description;
        $disease->remedies = $request->remedies;
        $disease->weather = $request->weather;
        $disease->image = $request->image;
        $disease->save();

        return redirect()->route('plant_diseases.index')->with('success', 'Plant disease updated successfully!');
    }

    public function destroy($id)
    {
        $disease = Disease::findOrFail($id);
        if ($disease) {
            $disease->delete();
        }
        return back()->with('error', 'Plant disease delete successfully!');
    }
}
