<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\BruneiPhone;
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
            'profile-photos/'.$user->id,
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
            'phone' => $this->bruneiPhoneRules(),
        ]);

        $user = $request->user();
        $incomingTrim = $this->trimmedPhone($request->input('phone'));
        $incomingNorm = $incomingTrim !== '' ? BruneiPhone::normalize($incomingTrim) : null;

        $user->update(['phone' => $incomingNorm]);

        return back()->with('status', 'profile-phone-updated');
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => $this->bruneiPhoneRules(),
        ];
        if ($request->hasFile('photo')) {
            $rules['photo'] = ['image', 'max:2048', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])];
        }
        $request->validate($rules);

        $user = $request->user();
        $incomingTrim = $this->trimmedPhone($request->input('phone'));
        $incomingNorm = $incomingTrim !== '' ? BruneiPhone::normalize($incomingTrim) : null;

        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store(
                'profile-photos/'.$user->id,
                'public'
            );
            $user->update([
                'name' => $request->name,
                'phone' => $incomingNorm,
                'profile_photo_path' => $path,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'phone' => $incomingNorm,
            ]);
        }

        return redirect()->route('customer.general', ['name' => $user->profileSlug()])
            ->with('status', 'profile-updated');
    }

    /**
     * @return list<string|\Closure>
     */
    private function bruneiPhoneRules(): array
    {
        return [
            'nullable',
            'string',
            'max:30',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || trim((string) $value) === '') {
                    return;
                }
                if (! BruneiPhone::isValid((string) $value)) {
                    $fail(__('Please enter a valid Brunei phone number (+673…).'));
                }
            },
        ];
    }

    private function trimmedPhone(mixed $phone): string
    {
        return is_string($phone) ? trim($phone) : '';
    }
}
