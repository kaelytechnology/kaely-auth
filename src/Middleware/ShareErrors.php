<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Share errors with all views
        View::share('errors', session('errors'));
        
        // Share success messages
        View::share('success', session('success'));
        
        // Share status messages
        View::share('status', session('status'));
        
        return $next($request);
    }
} 