<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $users = User::query()
            ->with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('pages.users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => $validated['is_active'],
            ]);

            $user->syncRoles([$validated['role']]);
        });

        return to_route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('pages.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        if ($user->is(auth()->user()) && ! (bool) $validated['is_active']) {
            return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        if ($user->is(auth()->user()) && ! $user->hasRole($validated['role'])) {
            return back()->withInput()->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
        }

        DB::transaction(function () use ($validated, $user) {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ];

            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }

            $user->update($data);
            $user->syncRoles([$validated['role']]);
        });

        return to_route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User berhasil {$status}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return to_route('users.index')->with('success', 'User berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
