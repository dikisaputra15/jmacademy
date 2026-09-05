<?php

namespace App\Http\Controllers;

use App\Models\CourseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('guru')) {
            $user->load('teachingCategories');
        }

        return view('pages.profile.edit', [
            'user' => $user,
            'teachingCategories' => $user->hasRole('guru')
                ? CourseCategory::where('is_active', true)->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Wilayah.id uses dotted codes (for example 32.73), while older data
        // sources use compact codes (3273). Keep one compact format internally.
        $request->merge([
            'province_id' => $this->normalizeRegionCode($request->input('province_id')),
            'regency_id' => $this->normalizeRegionCode($request->input('regency_id')),
        ]);

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

        if ($user->hasRole('guru')) {
            $rules['bank_name'] = ['nullable', 'string', 'max:100', 'required_with:bank_account_number,bank_account_holder'];
            $rules['bank_account_number'] = ['nullable', 'string', 'regex:/^[0-9\-\s]{6,50}$/', 'required_with:bank_name,bank_account_holder'];
            $rules['bank_account_holder'] = ['nullable', 'string', 'max:255', 'required_with:bank_name,bank_account_number'];
            $rules['teaching_category_ids'] = ['nullable', 'array'];
            $rules['teaching_category_ids.*'] = [
                'integer', 'distinct',
                Rule::exists('course_categories', 'id')->where('is_active', true),
            ];
        }

        $validated = $request->validate($rules, [
            'phone.regex' => 'Format nomor HP tidak valid.',
            'parent_phone.regex' => 'Format nomor HP orang tua tidak valid.',
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka, spasi, atau tanda hubung.',
            'bank_name.required_with' => 'Nama bank wajib dilengkapi bersama data rekening.',
            'bank_account_number.required_with' => 'Nomor rekening wajib dilengkapi bersama data rekening.',
            'bank_account_holder.required_with' => 'Nama pemilik rekening wajib dilengkapi bersama data rekening.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
        ]);

        $categoryIds = $validated['teaching_category_ids'] ?? [];
        unset($validated['teaching_category_ids']);

        DB::transaction(function () use ($user, $validated, $categoryIds) {
            $user->update($validated);

            if ($user->hasRole('guru')) {
                $user->teachingCategories()->sync($categoryIds);
            }
        });

        return to_route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    private function normalizeRegionCode(mixed $code): ?string
    {
        if (! is_string($code) || trim($code) === '') {
            return null;
        }

        return preg_replace('/\D+/', '', $code) ?: null;
    }
}
