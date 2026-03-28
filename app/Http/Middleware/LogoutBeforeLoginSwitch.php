<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogoutBeforeLoginSwitch
{
    /**
     * If user is already logged in and is submitting the login form (new credentials),
     * end the current session so the new login replaces it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->is('login') && Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        return $next($request);
    }
}
