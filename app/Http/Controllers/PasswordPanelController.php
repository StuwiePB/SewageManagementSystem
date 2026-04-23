<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordPanelController extends Controller
{
    public function checkCustomerEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        $exists = $user ? $user->hasRole(User::ROLE_CUSTOMER) : false;

        return response()->json([
            'exists' => $exists,
        ]);
    }

    public function updateCustomerPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'identifier.required' => 'Reset session expired. Start again from Forgot Password.',
        ]);

        $user = $this->resolveCustomerByIdentifier((string) $validated['identifier']);

        if (! $user) {
            return response()->json([
                'ok' => false,
                'message' => 'Unable to change password.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return response()->json([
            'ok' => true,
        ]);
    }

    private function resolveCustomerByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
            return ($user && $user->hasRole(User::ROLE_CUSTOMER)) ? $user : null;
        }

        $localDigits = $this->extractBruneiLocalDigits($identifier);
        if ($localDigits === null) {
            return null;
        }

        $fullDigits = '673'.$localDigits;
        $user = User::whereRaw(
            "REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), '+', ''), ' ', ''), '-', '') = ?",
            [$fullDigits]
        )->first();

        return ($user && $user->hasRole(User::ROLE_CUSTOMER)) ? $user : null;
    }

    private function extractBruneiLocalDigits(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (! is_string($digits) || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '673')) {
            $digits = substr($digits, 3);
        }

        return strlen($digits) === 7 ? $digits : null;
    }
}
