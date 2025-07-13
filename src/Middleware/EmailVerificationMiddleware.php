<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Kaely\Auth\Services\EmailVerificationService;

class EmailVerificationMiddleware
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if email verification is enabled
        if (!config('kaely-auth.email_verification.enabled', true)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            return $this->unauthenticatedResponse($request);
        }

        $user = Auth::user();

        // Check if email verification is required
        if (!config('kaely-auth.email_verification.required', false)) {
            return $next($request);
        }

        // Check if email is verified
        if (!$this->emailVerificationService->isEmailVerified($user)) {
            return $this->unverifiedResponse($request);
        }

        return $next($request);
    }

    /**
     * Handle unauthenticated response
     */
    protected function unauthenticatedResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Authentication required',
                'error' => 'unauthenticated'
            ], 401);
        }

        return redirect()->route('login');
    }

    /**
     * Handle unverified email response
     */
    protected function unverifiedResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Email verification required',
                'error' => 'email_unverified',
                'data' => [
                    'email' => Auth::user()->email,
                    'verified_at' => Auth::user()->email_verified_at,
                ]
            ], 403);
        }

        return redirect()->route('verification.notice');
    }
} 