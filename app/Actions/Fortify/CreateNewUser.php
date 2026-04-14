<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
<<<<<<< HEAD
=======
use Illuminate\Validation\Rule;
>>>>>>> 3wayfusionn-(use-this)
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
<<<<<<< HEAD
     * Validate and create a newly registered user.
=======
     * Validate and create a newly registered user (public customer signup).
>>>>>>> 3wayfusionn-(use-this)
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
<<<<<<< HEAD
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
=======
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
            'password' => $input['password'],
            'role' => User::ROLE_CUSTOMER,
        ]);

        $user->assignRole(User::ROLE_CUSTOMER);

        return $user;
>>>>>>> 3wayfusionn-(use-this)
    }
}
