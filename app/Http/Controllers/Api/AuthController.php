<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
  // POST /api/register
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'id'            => $request->id,
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => $request->password, // automatically hashed by model cast
            'role'          => $request->role ?? 'PATIENT',
            'department_id' => $request->department_id,
            'avatar'        => $request->avatar,
            'locale'        => $request->locale ?? 'en',
            'timezone'      => $request->timezone ?? 'Asia/Phnom_Penh',
            'is_active'     => true,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data'    => [
                'token' => $token,
                'user'  => $user,
            ],
        ], 201);
    }
    // POST /api/login
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is deactivated. Please contact support.',
            ], 403);
        }
        // Update last login timestamp
        $user->update(['last_login_at' => now()]);
        // Revoke old tokens & issue a new token
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'token' => $token,
                'user'  => $user,
            ],
        ]);
    }
    // GET /api/profile
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved.',
            'data'    => $request->user(),
        ]);
    }
    // PUT /api/profile
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => $user,
        ]);
    }
    // POST /api/logout
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
        // POST /api/forgot-password
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Generate a 64-character random token (or a 6-digit OTP)
        $token = Str::random(64);

        // Store hashed token in password_reset_tokens (replaces old token if exists)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        // In production: Send email to user with $token.
        // For testing in Postman, we return the token directly in the response:
        return response()->json([
            'success' => true,
            'message' => 'Password reset token created.',
            'data'    => [
                'email' => $request->email,
                'token' => $token, // Use this token in /api/reset-password
            ],
        ]);
    }

    // POST /api/reset-password
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        // Find token record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset token.',
            ], 400);
        }

        // Check if token expired (valid for 60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Password reset token has expired.',
            ], 400);
        }

        // Verify token hash
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token provided.',
            ], 400);
        }

        // Update user's password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => $request->password, // automatically hashed by User model cast
        ]);

        // Revoke all existing tokens (force re-login)
        $user->tokens()->delete();

        // Delete used reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been successfully reset. Please log in with your new password.',
        ]);
    }
        // POST /api/email/send-code
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already verified.',
            ], 400);
        }

        // Generate a 6-digit verification code
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(15); // Valid for 15 minutes

        // Insert or update verification code for this user
        \App\Models\EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code'       => $code,
                'expires_at' => $expiresAt,
            ]
        );

        // In production: Send the code via email/SMS.
        // For Postman testing, return the code in the response:
        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully.',
            'data'    => [
                'email'      => $user->email,
                'code'       => $code, // Use this code to verify
                'expires_at' => $expiresAt->toDateTimeString(),
            ],
        ]);
    }

    // POST /api/email/verify
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.',
            ], 400);
        }

        $verification = \App\Models\EmailVerification::where('user_id', $user->id)->first();

        if (!$verification || $verification->code !== $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 400);
        }

        if (now()->isAfter($verification->expires_at)) {
            $verification->delete();
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.',
            ], 400);
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
        ]);

        // Delete the used code
        $verification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'data'    => [
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }
}
