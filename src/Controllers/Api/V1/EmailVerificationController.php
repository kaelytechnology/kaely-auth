<?php

namespace Kaely\Auth\Controllers\Api\V1;

use Kaely\Auth\Controllers\Controller;
use Kaely\Auth\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        if ($this->emailVerificationService->isEmailVerified($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 400);
        }

        $success = $this->emailVerificationService->sendVerificationEmail($user);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send verification email'
        ], 400);
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $token = $request->input('token');
        $success = $this->emailVerificationService->verifyEmail($token);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification token'
        ], 400);
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        if ($this->emailVerificationService->isEmailVerified($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 400);
        }

        $success = $this->emailVerificationService->resendVerificationEmail($user);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Verification email resent successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to resend verification email'
        ], 400);
    }

    /**
     * Check email verification status
     */
    public function checkVerificationStatus(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $isVerified = $this->emailVerificationService->isEmailVerified($user);

        return response()->json([
            'success' => true,
            'data' => [
                'is_verified' => $isVerified,
                'email' => $user->email,
                'verified_at' => $user->email_verified_at,
            ]
        ]);
    }
} 