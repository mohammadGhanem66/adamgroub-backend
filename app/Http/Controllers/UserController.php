<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $users = User::where('is_admin', 0)->get();
        Log::info("All users fetched ..!");
        return response()->json([
            'users' => $users,
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
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required|min:8',
            'address' => 'nullable',
            'city' => 'nullable',
        ]);
        try{
            $user = User::create(array_merge($validated, ['is_admin' => 0]));
            Log::info("User created ..!, ".$user->name);
            return response()->json([
                'user' => $user,
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not created '.$e->getMessage(),
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
            $user = User::findOrFail($id);
            Log::info("User fetched ..!, ".$user->name);
            return response()->json([
                'user' => $user,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found',
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // write the logic here to update the user !
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required|min:8',
            'address' => 'nullable',
            'city' => 'nullable',
        ]);
        try{
            $user = User::findOrFail($id);
            $user->update($validated);
            Log::info("User updated ..!, ".$user->name);
            return response()->json([
                'user' => $user,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function resetPassword(Request $request, string $id){
        $validated = $request->validate([
            'password' => 'required|min:8',
        ]);
        try{
            $user = User::findOrFail($id);
            $user->password = Hash::make($validated['password']);
            $user->save();
            Log::info("User password reset ..!, ".$user->name);
            return response()->json([
                 "message" => "Resource updated successfully.",
                'user' => $user,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found',
            ]);
        }
    }
}
