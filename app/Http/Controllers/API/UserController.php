<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a paginated listing of users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get users with pagination (10 per page)
        $users = User::paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $users
        ], 200);
    }

    /**
     * Get paginated users with optional search and filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPaginatedUsers(Request $request)
    {
        try {
            // Default to page 1 if not specified
            $page = $request->input('page', 1);
            
            // Fixed items per page to 10 as requested
            $perPage = 10;
            
            // Start with a base query
            $query = User::query();
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('mobile', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Get the total count for pagination
            $totalUsers = $query->count();
            $totalPages = ceil($totalUsers / $perPage);
            
            // Get users for current page
            $users = $query->skip(($page - 1) * $perPage)
                         ->take($perPage)
                         ->get();
            
            // Format the users for the frontend, similar to your sample data
            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => 'USR' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                    'fullName' => $user->first_name && $user->last_name ? 
                        $user->first_name . ' ' . $user->last_name : $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? $user->mobile ?? 'Not provided',
                    'address' => $this->formatAddress($user),
                    'registrationDate' => $user->created_at->format('M d, Y')
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'users' => $formattedUsers,
                    'pagination' => [
                        'currentPage' => (int)$page,
                        'totalPages' => $totalPages,
                        'perPage' => $perPage,
                        'totalUsers' => $totalUsers
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create the user
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            
            // Optional fields
            if ($request->has('first_name')) $user->first_name = $request->first_name;
            if ($request->has('last_name')) $user->last_name = $request->last_name;
            if ($request->has('mobile')) $user->mobile = $request->mobile;
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('country')) $user->country = $request->country;
            if ($request->has('company')) $user->company = $request->company;
            if ($request->has('address')) $user->address = $request->address;
            if ($request->has('apartment')) $user->apartment = $request->apartment;
            if ($request->has('city')) $user->city = $request->city;
            if ($request->has('state')) $user->state = $request->state;
            if ($request->has('postal_code')) $user->postal_code = $request->postal_code;
            
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $user = User::findOrFail(auth()->id());
            
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'email' => [
                    'nullable',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore(auth()->id()),
                ],
                'password' => 'nullable|string|min:8',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'mobile' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'apartment' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Update user fields if they are provided
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password')) $user->password = Hash::make($request->password);
            if ($request->has('first_name')) $user->first_name = $request->first_name;
            if ($request->has('last_name')) $user->last_name = $request->last_name;
            if ($request->has('mobile')) $user->mobile = $request->mobile;
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('country')) $user->country = $request->country;
            if ($request->has('company')) $user->company = $request->company;
            if ($request->has('address')) $user->address = $request->address;
            if ($request->has('apartment')) $user->apartment = $request->apartment;
            if ($request->has('city')) $user->city = $request->city;
            if ($request->has('state')) $user->state = $request->state;
            if ($request->has('postal_code')) $user->postal_code = $request->postal_code;
            
            $user->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Helper function to format user address
     * 
     * @param \App\Models\User $user
     * @return string
     */
    private function formatAddress($user)
    {
        $addressParts = [];
        
        if ($user->address) $addressParts[] = $user->address;
        if ($user->apartment) $addressParts[] = $user->apartment;
        if ($user->city) $addressParts[] = $user->city;
        if ($user->state) $addressParts[] = $user->state;
        if ($user->postal_code) $addressParts[] = $user->postal_code;
        if ($user->country) $addressParts[] = $user->country;
        
        return !empty($addressParts) ? implode(', ', $addressParts) : 'Not provided';
    }
}