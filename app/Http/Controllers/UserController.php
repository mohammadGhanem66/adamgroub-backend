<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_device;
use Google\Client;
use Hash;
use Http;
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
        try{
            $user = User::findOrFail($id);
            $user->containers()->delete();
            $user->account_statments()->delete();
            $user->delete();
            Log::info("User deleted ..!, ".$user->name);
            return response()->json([
                "message" => "Resource deleted successfully.",
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found',
            ]);
        }
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
    public function getUserContainers(string $id){
        try{
            $user = User::findOrFail($id);
            $containers = $user->containers;
            Log::info("User containers fetched ..!, ".$user->name);
            return response()->json([
                'containers' => $containers,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found',
            ]);
        }
    }
    public function getContainersForLoggedUser(Request $request){
        $user = auth()->user();
        $containers = $user->containers;
        Log::info("User containers fetched ..!, ".$user->name);
        return response()->json([
            'containers' => $containers,
        ], 200);
    }
    public function getAccountStatmentsForLoggedUser(Request $request){
        $user = auth()->user();
        $account_statments = $user->account_statments;
        Log::info("User account_statments fetched ..!, ".$user->name);
        return response()->json([
            'account_statments' => $account_statments,
        ], 200);
    }
    public function changeMobile(Request $request){
        $user = auth()->user();
        $request->validate([
            'phone' => 'required|string|regex:/^(\+?\d{1,4}[\s-])?(?!0+\s+,?$)\d{10,13}$/|unique:users,phone',
        ]);
        $user->phone = $request->phone;
        $user->save();
        Log::info("User mobile changed ..!, ".$user->name);
        return response()->json([
            'status' => true,
            'message' => 'Mobile number updated successfully',
            'phone' => $user->phone,
        ], 200);
    }
    public function sendNotification(Request $request){
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);
        // Retrieve device tokens for the provided user IDs
        $tokens = User_device::whereIn('user_id', $request->user_ids)->pluck('device_token')->toArray();
        if (empty($tokens)) {
            return response()->json(['status' => false, 'message' => 'No ID found for the provided user IDs.'], 404);
        }

        $accessToken = $this->getAccessToken();  // Generate the OAuth2 token

        $notificationData = [
            'tokens' => $tokens,
            'notification' => [
                'title' => $request->subject,
                'body' => $request->message,
                'sound' => 'default',
            ]
        ];
       
         // Send the notification using HTTP v1 API
        $response = Http::withToken($accessToken)
        ->post('https://fcm.googleapis.com/v1/projects/YOUR_PROJECT_ID/messages:send', $notificationData);

        if ($response->successful()) {
            return response()->json(['status' => true, 'message' => 'Notifications sent successfully.']);
        } else {
            return response()->json(['status' => false, 'message' => 'Failed to send notifications.', 'error' => $response->body()], 500);
        }
        
    }

    private function getAccessToken()
{
    $client = new Client();
    $client->setAuthConfig(storage_path('fcm-service-account.json'));
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

    return $client->fetchAccessTokenWithAssertion()['access_token'];
}
    public function StoreMobileToken(Request $request){
        $request->validate([
            'device_token' => 'required|string|max:255',
        ]);
        $user = auth()->user();
        $devices = User_device::where('device_token', $request->device_token)->first();
        if (!$devices) {
            $user->user_devices()->create([
                'device_token' => $request->device_token,
            ]);
        }
        Log::info("User token stored ..!, ".$user->name);
        return response()->json([
            'status' => true,
            'message' => 'Token stored successfully',
        ], 200);
    }
    public function getUserAccountStatments(string $id){
        try{
            $user = User::findOrFail($id);
            $account_statments = $user->account_statments;
            Log::info("User account_statments fetched ..!, ".$user->name);
            return response()->json([
                'account_statments' => $account_statments,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found',
            ]);
        }
    }
    public function getUploadedFile(Request $request, string $id){
        try{
            $user = User::findOrFail($id);
            $files = $user->files();
            Log::info("User files fetched ..!, ".$user->name);
            return response()->json([
                'files' => $files,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'User not found'.$e    ,
            ]);
        }
    }
}
