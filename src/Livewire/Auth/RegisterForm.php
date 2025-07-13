<?php

namespace Kaely\Auth\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RegisterForm extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $terms = false;
    public $showPassword = false;
    public $showPasswordConfirmation = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'terms' => 'required|accepted',
    ];

    protected $messages = [
        'name.required' => 'Name is required.',
        'name.max' => 'Name must not exceed 255 characters.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already registered.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.confirmed' => 'Password confirmation does not match.',
        'terms.required' => 'You must accept the terms and conditions.',
        'terms.accepted' => 'You must accept the terms and conditions.',
    ];

    public function mount()
    {
        // Check if user is already logged in
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }
    }

    public function register()
    {
        $this->validate();

        // Check rate limiting
        $this->ensureIsNotRateLimited();

        // Create user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => config('kaely-auth.email_verification.enabled') ? null : now(),
        ]);

        // Log in user
        Auth::login($user);

        // Log registration activity
        $this->logRegistrationActivity($user);

        // Clear rate limiting
        RateLimiter::clear($this->throttleKey());

        // Redirect to dashboard
        return redirect()->intended(route('dashboard'));
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePasswordConfirmation()
    {
        $this->showPasswordConfirmation = !$this->showPasswordConfirmation;
    }

    public function redirectToOAuth($provider)
    {
        return redirect()->route('oauth.redirect', $provider);
    }

    protected function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
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
        return 'register|' . request()->ip();
    }

    protected function logRegistrationActivity($user)
    {
        // Log successful registration activity
        \DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'action' => 'user.registered',
            'description' => 'User registered successfully',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'tenant_id' => request()->input('current_tenant.id'),
            'metadata' => json_encode([
                'registration_method' => 'email',
                'email_verified' => !config('kaely-auth.email_verification.enabled'),
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
        return view('kaely-auth::livewire.auth.register-form');
    }
} 