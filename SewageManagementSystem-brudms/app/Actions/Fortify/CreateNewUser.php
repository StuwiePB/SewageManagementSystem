<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user (public customer signup).
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => $this->nameRules(),
            'phone' => [
                'required',
                'string',
                'max:13',
                'regex:/^\+673\s\d{3}\s\d{4}$/',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $inputPhone = (string) ($input['phone'] ?? '');
        $phoneDigits = preg_replace('/\D+/', '', $inputPhone);
        if (str_starts_with($phoneDigits, '673')) {
            $phoneDigits = substr($phoneDigits, 3);
        }
        $phoneDigits = substr($phoneDigits ?: '', 0, 7);
        $normalizedPhone = '+673 '.substr($phoneDigits, 0, 3).' '.substr($phoneDigits, 3, 4);

        $user = User::create([
            'name' => $input['name'],
            'email' => null,
            'phone' => $normalizedPhone,
            'password' => $input['password'],
            'role' => User::ROLE_CUSTOMER,
        ]);

        $user->assignRole(User::ROLE_CUSTOMER);

        return $user;
    }
}
