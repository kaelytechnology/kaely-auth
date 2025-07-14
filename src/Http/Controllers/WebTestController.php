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
            return view('kaely-auth::blade.auth.login');
        } catch (\Exception $e) {
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