<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerNameInUrl
{
    /**
     * Ensure the {name} param in the URL matches the logged-in customer's profile slug.
     * Redirect to the correct URL if it doesn't match.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $name = $request->route('name');

        if (!$user || !$name) {
            return $next($request);
        }

        if ($user->profileSlug() !== $name) {
            $routeName = $request->route()->getName();
            $params = $request->route()->parameters();
            $params['name'] = $user->profileSlug();

            return redirect()->route($routeName, $params);
        }

        return $next($request);
    }
}
