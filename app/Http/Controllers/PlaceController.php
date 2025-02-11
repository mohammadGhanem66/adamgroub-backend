<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;
use Log;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $places = Place::all();
        Log::info("All places fetched ..!");
        return response()->json([
            'places' => $places,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required',
            'image_name' => 'required',
            'image_path' => 'required|file|mimes:png,jpg,jpeg,gif,svg',
            'description' => 'nullable',
            'location' => 'nullable',
            'city' => 'required',
            'country' => 'required',
        ]);
        try{
            $file = $request->file('image_path');
            $path = $file->store('places', 'public');
            $place = Place::create(array_merge($validated, ['image_path' => $path]));
            Log::info("Place created ..!, ".$place->name);
            return response()->json([
                'place' => $place,
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Place not created '.$e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try{
            $place = Place::findOrFail($id);
            Log::info("Place fetched ..!, ".$place->name);
            return response()->json([
                'place' => $place,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Place not found',
            ]);
        }
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validated = $request->validate([
            'name' => 'required',
            'image_name' => 'required',
            'image_path' => 'required|file|mimes:png,jpg,jpeg,gif,svg',
            'description' => 'nullable',
            'location' => 'nullable',
            'city' => 'required',
            'country' => 'required',
        ]);
        try{
            $place = Place::findOrFail($id);
            $file = $request->file('image_path');
            $path = $file->store('places', 'public');
            $place->update(array_merge($validated, ['image_path' => $path]));
            Log::info("Place updated ..!, ".$place->name);
            return response()->json([
                'place' => $place,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Place not updated '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
