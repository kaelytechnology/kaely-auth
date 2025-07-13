<?php

namespace Kaely\Auth\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginForm extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $showPassword = false;

    protected $rules = [
        'email' => 'required|email|max:255',
        'password' => 'required|min:8',
    ];

    protected $messages = [
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.max' => 'Email must not exceed 255 characters.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
    ];

    public function mount()
    {
        // Check if user is already logged in
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }
    }

    public function login()
    {
        $this->validate();

        // Check rate limiting
        $this->ensureIsNotRateLimited();

        // Attempt to authenticate
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Clear rate limiting
            RateLimiter::clear($this->throttleKey());

            // Log successful login
            $this->logLoginActivity();

            // Redirect to intended page or dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Increment rate limiting
        RateLimiter::hit($this->throttleKey());

        // Log failed login attempt
        $this->logFailedLoginActivity();

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function redirectToOAuth($provider)
    {
        return redirect()->route('oauth.redirect', $provider);
    }

    protected function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey()
    {
        return mb_strtolower($this->email) . '|' . request()->ip();
    }

    protected function logLoginActivity()
    {
        // Log successful login activity
        \DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'action' => 'user.login',
            'description' => 'User logged in successfully',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'tenant_id' => request()->input('current_tenant.id'),
            'metadata' => json_encode([
                'login_method' => 'email',
                'two_factor_enabled' => false,
                'location' => $this->getLocation(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function logFailedLoginActivity()
    {
        // Log failed login attempt
        \DB::table('audit_logs')->insert([
            'user_id' => null,
            'action' => 'user.login_failed',
            'description' => 'Failed login attempt with invalid credentials',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => null,
            'tenant_id' => request()->input('current_tenant.id'),
            'metadata' => json_encode([
                'attempted_email' => $this->email,
                'reason' => 'invalid_credentials',
                'location' => $this->getLocation(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function getLocation()
    {
        // This would typically use a geolocation service
        // For now, return a placeholder
        return 'Unknown Location';
    }

    public function render()
    {
        return view('kaely-auth::livewire.auth.login-form');
    }
} 