<?php

namespace App\Http\Responses;

use App\Models\User;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect to the dashboard for the user's role after login.
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) || $user->hasRole(User::ROLE_ADMIN)) {
            return redirect()->intended(route('admin.dashboard'));
        }
        if ($user->hasRole(User::ROLE_OPERATOR)) {
            return redirect()->intended(route('operator.dashboard'));
        }
        if ($user->hasRole(User::ROLE_CUSTOMER)) {
            return redirect()->intended(route('customer.dashboard', ['name' => $user->profileSlug()]));
        }

        return redirect()->intended('/dashboard');
    }
}
