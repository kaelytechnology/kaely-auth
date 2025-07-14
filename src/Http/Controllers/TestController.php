<?php

namespace Kaely\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    /**
     * Test endpoint to verify the package is working.
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'KaelyAuth package is working correctly',
            'timestamp' => now(),
            'user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ] : null
        ]);
    }

    /**
     * Test login endpoint.
     */
    public function testLogin(Request $request)
    {
        try {
            \Log::info('KaelyAuth Test Login - Request received', [
                'email' => $request->email,
                'has_password' => $request->has('password'),
                'headers' => $request->headers->all()
            ]);

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');
            
            \Log::info('KaelyAuth Test Login - Attempting authentication', [
                'email' => $credentials['email']
            ]);

            if (!Auth::attempt($credentials)) {
                \Log::warning('KaelyAuth Test Login - Authentication failed', [
                    'email' => $credentials['email']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'debug' => [
                        'email_provided' => $request->email,
                        'password_provided' => $request->has('password'),
                        'auth_guard' => config('auth.defaults.guard')
                    ]
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('test_token')->plainTextToken;

            \Log::info('KaelyAuth Test Login - Authentication successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            \Log::error('KaelyAuth Test Login - Exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 