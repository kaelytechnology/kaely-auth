<?php

namespace Kaely\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SimpleLoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        Log::info('KaelyAuth: SimpleLoginController - showLoginForm called');
        
        try {
            return view('kaely-auth::blade.auth.login', [
                'errors' => session('errors'),
                'status' => session('status')
            ]);
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error in SimpleLoginController', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response('Error loading login form: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle login form submission.
     */
    public function login(Request $request)
    {
        Log::info('KaelyAuth: SimpleLoginController - login attempt', [
            'email' => $request->email,
            'has_password' => !empty($request->password)
        ]);

        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            Log::info('KaelyAuth: Attempting authentication', [
                'email' => $request->email,
                'remember' => $remember
            ]);

            if (!Auth::attempt($credentials, $remember)) {
                Log::warning('KaelyAuth: Authentication failed', ['email' => $request->email]);
                
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                    ]);
            }

            $user = Auth::user();
            Log::info('KaelyAuth: Authentication successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error during login', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Error durante el inicio de sesión: ' . $e->getMessage(),
                ]);
        }
    }

    /**
     * Show a simple test page.
     */
    public function test()
    {
        return response()->json([
            'message' => 'KaelyAuth SimpleLoginController is working',
            'timestamp' => now(),
            'auth_check' => Auth::check(),
            'user' => Auth::user()
        ]);
    }
} 