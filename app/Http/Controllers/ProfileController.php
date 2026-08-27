<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pages.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+()\-\s]{8,20}$/'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'province_id' => ['nullable', 'required_with:province_name,regency_id', 'string', 'regex:/^[0-9]{2}$/'],
            'province_name' => ['nullable', 'required_with:province_id', 'string', 'max:100'],
            'regency_id' => ['nullable', 'required_with:regency_name', 'string', 'regex:/^[0-9]{4}$/'],
            'regency_name' => ['nullable', 'required_with:regency_id', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'postal_code' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
        ];

        if ($user->hasRole('student')) {
            $rules['parent_name'] = ['nullable', 'string', 'max:255'];
            $rules['parent_phone'] = ['nullable', 'string', 'regex:/^[0-9+()\-\s]{8,20}$/'];
        }

        $validated = $request->validate($rules, [
            'phone.regex' => 'Format nomor HP tidak valid.',
            'parent_phone.regex' => 'Format nomor HP orang tua tidak valid.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
        ]);

        $user->update($validated);

        return to_route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
