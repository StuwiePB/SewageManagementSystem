<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function bindEmail(Request $request): JsonResponse
    {
        $normalizedEmail = trim((string) $request->input('email', ''));
        $normalizedEmail = strtolower($normalizedEmail);

        if ($normalizedEmail === '' || $normalizedEmail === 'null') {
            return response()->json([
                'ok' => false,
                'message' => 'Email is required.',
            ], 422);
        }

        $validated = $request->merge([
            'email' => $normalizedEmail,
        ])->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
        ]);

        $user = $request->user();
        $user->update([
            'email' => $validated['email'],
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'email' => $user->email,
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])],
        ]);

        $user = $request->user();

        // Delete previous photo if any
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('photo')->store(
            'profile-photos/' . $user->id,
            'public'
        );

        $user->update(['profile_photo_path' => $path]);

        return back()->with('status', 'profile-photo-updated');
    }

    public function updateName(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update(['name' => $request->name]);

        return back()->with('status', 'profile-name-updated');
    }

    public function updatePhone(Request $request)
    {
        return back()->withErrors([
            'phone' => 'Phone number is bound to your account and cannot be changed.',
        ]);
    }

    public function update(Request $request)
    {
        $normalizedEmail = trim((string) $request->input('email', ''));
        $normalizedEmailLower = strtolower($normalizedEmail);
        $normalizedEmail = ($normalizedEmail === '' || $normalizedEmailLower === 'null')
            ? null
            : $normalizedEmailLower;

        $currentEmail = $request->user()->email;
        $currentEmailNormalized = is_string($currentEmail) ? strtolower(trim($currentEmail)) : null;
        if ($currentEmailNormalized === 'null') {
            $currentEmailNormalized = null;
        }

        // Email changes are not allowed from "Save Changes". User must use Bind flow first.
        if ($normalizedEmail !== $currentEmailNormalized) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Bind your email first before saving profile changes.',
                ]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
        ];
        if ($request->hasFile('photo')) {
            $rules['photo'] = ['image', 'max:2048', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])];
        }
        $validated = $request->merge([
            'email' => $normalizedEmail,
        ])->validate($rules);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store(
                'profile-photos/' . $user->id,
                'public'
            );
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'profile_photo_path' => $path,
            ]);
        } else {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
            ]);
        }

        return redirect()->route('customer.general', ['name' => $user->profileSlug()])
            ->with('status', 'profile-updated');
    }
}
