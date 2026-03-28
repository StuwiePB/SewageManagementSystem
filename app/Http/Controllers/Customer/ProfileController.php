<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ProfileController extends Controller
{
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
        $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update(['phone' => $request->phone ?: null]);

        return back()->with('status', 'profile-phone-updated');
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
        if ($request->hasFile('photo')) {
            $rules['photo'] = ['image', 'max:2048', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])];
        }
        $request->validate($rules);

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
                'name' => $request->name,
                'phone' => $request->phone ?: null,
                'profile_photo_path' => $path,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone ?: null,
            ]);
        }

        return redirect()->route('customer.general', ['name' => $user->profileSlug()])
            ->with('status', 'profile-updated');
    }
}
