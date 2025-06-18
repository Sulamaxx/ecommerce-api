<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    /**
     * Display a paginated listing of staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get staff members with pagination (10 per page)
        $staff = User::where('user_type', 'staff')->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $staff
        ], 200);
    }

    /**
     * Get paginated staff members with optional search and filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPaginatedStaff(Request $request)
    {
        try {
            // Default to page 1 if not specified
            $page = $request->input('page', 1);
            
            // Fixed items per page to 10 as requested
            $perPage = 10;
            
            // Start with a base query for staff members only
            $query = User::where('user_type', 'staff');
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('mobile', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('company', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Get the total count for pagination
            $totalStaff = $query->count();
            $totalPages = ceil($totalStaff / $perPage);
            
            // Get staff for current page
            $staff = $query->skip(($page - 1) * $perPage)
                         ->take($perPage)
                         ->get();
            
            // Format the staff for the frontend
            $formattedStaff = $staff->map(function ($staffMember) {
                return [
                    'id' => $staffMember->id,
                    'firstName' => $staffMember->first_name,
                    'lastName'=>$staffMember->last_name,
                    'email' => $staffMember->email,
                    'mobile' => $staffMember->mobile ?? 'Not provided',
                    'company' => $staffMember->company ?? 'Not provided',
                    'registrationDate' => $staffMember->created_at->format('M d, Y')
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'staff' => $formattedStaff,
                    'pagination' => [
                        'currentPage' => (int)$page,
                        'totalPages' => $totalPages,
                        'perPage' => $perPage,
                        'totalStaff' => $totalStaff
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve staff members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data - only the specified fields for staff
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'mobile' => 'required|string|max:20',
            'company' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create the staff member
            $staff = new User();
            $staff->first_name = $request->firstName;
            $staff->last_name = $request->lastName;
            $staff->name = $request->first_name;
            $staff->email = $request->email;
            $staff->password = Hash::make($request->password);
            $staff->mobile = $request->mobile;
            $staff->company = $request->company;
            $staff->user_type = 'staff'; // Set user type as staff
            
            $staff->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member created successfully',
                'data' => $staff
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified staff member.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $staff = User::where('user_type', 'staff')->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $staff
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff member not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $staff = User::where('user_type', 'staff')->findOrFail($id);
            
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'email' => [
                    'nullable',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($id),
                ],
                'password' => 'nullable|string|min:8',
                'mobile' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Update staff fields if they are provided
            if ($request->has('firstName')) $staff->first_name = $request->firstName;
            if ($request->has('lastName')) $staff->last_name = $request->lastName;
            if ($request->has('firstName'))  $staff->name = $request->firstName;
            if ($request->has('email')) $staff->email = $request->email;
            if ($request->has('password')) {
                if(trim($request->password) !== '') {
                    // Only hash the password if it is provided
                    $staff->password = Hash::make($request->password);
                }
            }
            if ($request->has('mobile')) $staff->mobile = $request->mobile;
            if ($request->has('company')) $staff->company = $request->company;
            
            // Update the name field if first_name or last_name is updated
            if ($request->has('first_name') || $request->has('last_name')) {
                $staff->name = $staff->first_name . ' ' . $staff->last_name;
            }
            
            $staff->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Staff member updated successfully',
                'data' => $staff
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update staff member',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Remove the specified staff member from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $staff = User::where('user_type', 'staff')->findOrFail($id);
            $staff->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Staff member deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete staff member',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }
}