<?php

namespace Kaely\Auth\Controllers\Api\V1;

use Kaely\Auth\Controllers\Controller;
use Kaely\Auth\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Send password reset email
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->input('email');
        $success = $this->passwordResetService->sendResetEmail($email);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send password reset link'
        ], 400);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');

        $success = $this->passwordResetService->resetPassword($email, $token, $password);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ], 400);
    }

    /**
     * Validate reset token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->input('email');
        $token = $request->input('token');

        $isValid = $this->passwordResetService->validateToken($email, $token);

        if ($isValid) {
            return response()->json([
                'success' => true,
                'message' => 'Token is valid'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired token'
        ], 400);
    }
} 