<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Support\BruneiPhone;
use App\Support\PhoneVerificationSession;
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
            ...$this->profileRules(),
            'phone' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $phone = (string) $value;
                    if (! BruneiPhone::isValid($phone)) {
                        $fail(__('Please enter a valid Brunei phone number (+673...).'));

                        return;
                    }
                    $normalized = BruneiPhone::normalize($phone);
                    if (! PhoneVerificationSession::isVerifiedFor($normalized)) {
                        $fail(__('Please verify your phone number with OTP before creating account.'));
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => BruneiPhone::normalize((string) $input['phone']),
            'password' => $input['password'],
            'role' => User::ROLE_CUSTOMER,
        ]);

        $user->assignRole(User::ROLE_CUSTOMER);
        PhoneVerificationSession::clear();

        return $user;
    }
}
