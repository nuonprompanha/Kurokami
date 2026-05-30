<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');

        $users = User::query()
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => User::roles(),
            'currentRole' => $role,
            'search' => $request->query('search'),
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => User::roles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        User::query()->create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::roles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if ($user->id === Auth::id() && $validated['role'] !== User::ROLE_ADMINISTRATOR) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'You cannot change your own role from Administrator.']);
        }

        if ($user->isAdministrator() && $validated['role'] !== User::ROLE_ADMINISTRATOR) {
            $adminCount = User::query()->where('role', User::ROLE_ADMINISTRATOR)->count();

            if ($adminCount <= 1) {
                return back()
                    ->withInput()
                    ->withErrors(['role' => 'At least one Administrator account is required.']);
            }
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->isAdministrator()) {
            $adminCount = User::query()->where('role', User::ROLE_ADMINISTRATOR)->count();

            if ($adminCount <= 1) {
                return back()->withErrors(['user' => 'Cannot delete the last Administrator account.']);
            }
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account deleted successfully.');
    }
}
