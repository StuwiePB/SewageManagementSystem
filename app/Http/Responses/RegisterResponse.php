<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Redirect to customer dashboard after successful registration.
     */
    public function toResponse($request): Response
    {
        $request->session()->forget('email_bind_prompt_dismissed');

        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        return redirect()->route('customer.dashboard', ['name' => $request->user()->profileSlug()]);
    }
}
