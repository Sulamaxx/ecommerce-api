<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login'], 401);
        }
        
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('api-token')->plainTextToken;
        
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'user_type' => $user->user_type,
                'profile_picture' => $user->profile_picture
            ]
        ]);
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'mobile' => ['required', 'regex:/^0[7-9]{1}[0-9]{8}$/'],
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ], [
            'mobile.regex' => 'Please enter a valid Sri Lankan mobile number.',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
        
        return response()->json($user);
    }

    /**
     * Send password reset code via email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        // Delete any existing password reset tokens for this user
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Generate a random 6-digit code
        $code = mt_rand(100000, 999999);

        // Insert reset code into database
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($code), // Store hashed token
            'created_at' => Carbon::now()
        ]);

        // Send email with verification code
        try {
            // In a real app, you'd use a proper email template
            Mail::raw("Your password reset code is: $code", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Password Reset Verification Code');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset code has been sent to your email'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify the password reset code
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        // Get the token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Check if token exists and is valid
        if (!$tokenRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification code'
            ], 400);
        }

        // Check if token is expired (1 hour limit)
        if (Carbon::parse($tokenRecord->created_at)->addHour()->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
                
            return response()->json([
                'status' => 'error',
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Verify the code matches
        if (!Hash::check($request->code, $tokenRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification code'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification code validated successfully'
        ]);
    }

    /**
     * Reset password with verification code
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get the token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Check if token exists and is valid
        if (!$tokenRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification code'
            ], 400);
        }

        // Check if token is expired (1 hour limit)
        if (Carbon::parse($tokenRecord->created_at)->addHour()->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
                
            return response()->json([
                'status' => 'error',
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Verify the code matches
        if (!Hash::check($request->code, $tokenRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Update the user's password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully'
        ]);
    }
}