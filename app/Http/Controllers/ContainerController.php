<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Log;

class ContainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $user_id)
    {
        //
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|file|mimes:doc,docx,pdf,xls,xlsx,png,jpg,jpeg,gif,svg|max:2048', // Add file validation
        ]);
        try{
            $user = User::findOrFail($user_id);
            $file = $request->file('file_path');
            $fileName = $file->getClientOriginalName();
            $path = $file->store('containers', 'public');

            // Create container associated with the user
            $container = $user->containers()->create([
                'file_name' => $validated['file_name'],
                'file_path' => $path,
            ]);

            Log::info("Container created ..!, ".$container->file_name);

            return response()->json([
                'message' => 'Container created successfully.',
                'container' => $container,
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Container not created '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
