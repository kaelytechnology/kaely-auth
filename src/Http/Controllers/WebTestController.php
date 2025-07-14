<?php

namespace Kaely\Auth\Http\Controllers;

use Illuminate\Http\Request;

class WebTestController extends Controller
{
    /**
     * Test simple view.
     */
    public function test()
    {
        return response('KaelyAuth Web Test - Package is working!');
    }

    /**
     * Test login view with error handling.
     */
    public function testLogin()
    {
        try {
            \Log::info('KaelyAuth Web Test - Loading login view');
            
            if (!view()->exists('kaely-auth::blade.auth.login')) {
                \Log::error('KaelyAuth Web Test - Login view not found');
                return response('Login view not found. Check if package is properly installed.', 500);
            }
            
            return view('kaely-auth::blade.auth.login');
        } catch (\Exception $e) {
            \Log::error('KaelyAuth Web Test - Error loading login view', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response('Error loading login view: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Test register view with error handling.
     */
    public function testRegister()
    {
        try {
            return view('kaely-auth::blade.auth.register');
        } catch (\Exception $e) {
            return response('Error loading register view: ' . $e->getMessage(), 500);
        }
    }
} 