<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of user accounts in the merged Admin Credentials hub.
     */
    public function index(Request $request): View
    {
        $user = Auth::user()->load('roles');
        $activeTab = 'users';

        $search = $request->query('search');
        $roleFilter = $request->query('role');

        $usersQuery = User::with('roles')->latest();

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $usersQuery->whereHas('roles', function ($query) use ($roleFilter) {
                $query->where('roles.id', $roleFilter)
                    ->orWhere('roles.slug', $roleFilter);
            });
        }

        $users = $usersQuery->paginate(12)->withQueryString();
        $roles = Role::withCount('users')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $totalUsers = User::count();
        $totalRoles = $roles->count();
        $adminCount = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['it-admin', 'warehouse-admin']);
        })->count();
        $staffCount = User::whereHas('roles', function ($query) {
            $query->where('slug', 'warehouse-staff');
        })->count();
        $salesCount = User::whereHas('roles', function ($query) {
            $query->where('slug', 'sales-executive');
        })->count();

        $recentUsers = User::with('roles')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.settings.credentials', [
            'user' => $user,
            'users' => $users,
            'roles' => $roles,
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'adminCount' => $adminCount,
            'staffCount' => $staffCount,
            'salesCount' => $salesCount,
            'recentUsers' => $recentUsers,
            'search' => $search,
            'selectedRole' => $roleFilter,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Store a newly created user account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('status', "User account '{$user->name}' was successfully created.");
    }

    /**
     * Update the specified user account.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['roles'])) {
            // Prevent removing last admin role if this is the only IT admin
            $itAdminRole = Role::where('slug', 'it-admin')->first();
            if ($itAdminRole && $user->roles->contains($itAdminRole->id) && ! in_array($itAdminRole->id, $validated['roles'])) {
                $otherItAdmins = User::whereHas('roles', fn ($q) => $q->where('slug', 'it-admin'))
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($otherItAdmins === 0) {
                    return back()->withErrors(['error' => 'Cannot remove IT Administrator role: at least one active IT Admin must remain in the system.']);
                }
            }

            $user->roles()->sync($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('status', "User account '{$user->name}' was successfully updated.");
    }

    /**
     * Remove the specified user account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own currently active administrator account.']);
        }

        // Prevent deleting the last remaining IT admin
        $isItAdmin = $user->hasRole('it-admin');
        if ($isItAdmin) {
            $totalItAdmins = User::whereHas('roles', fn ($q) => $q->where('slug', 'it-admin'))->count();
            if ($totalItAdmins <= 1) {
                return back()->withErrors(['error' => 'Cannot delete this user because they are the last remaining IT Administrator in the system.']);
            }
        }

        $userName = $user->name;
        $user->roles()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', "User '{$userName}' has been deleted successfully.");
    }
}
