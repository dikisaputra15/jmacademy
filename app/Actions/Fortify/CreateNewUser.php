<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = Str::lower(trim($input['email']));
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc,strict',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{8,20}$/'],
            'address' => ['required', 'string', 'max:1000'],
        ], [
            'email.email' => 'Masukkan alamat email yang valid, misalnya nama@gmail.com.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan masuk.',
            'phone.regex' => 'Format nomor HP tidak valid.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'address' => $input['address'],
                'password' => Hash::make($input['password']),
            ]);

            $user->assignRole('student');

            return $user;
        });
    }
}
