<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'role' => ['required', Rule::in(['guru', 'student']), Rule::exists('roles', 'name')],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{8,20}$/'],
            'address' => ['required', 'string', 'max:1000'],
        ], [
            'role.required' => 'Silakan pilih daftar sebagai guru atau student.',
            'role.in' => 'Role pendaftaran tidak valid.',
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

            $user->assignRole($input['role']);

            return $user;
        });
    }
}
