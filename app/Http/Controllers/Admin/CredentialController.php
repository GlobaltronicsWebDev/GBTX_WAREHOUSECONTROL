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
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CredentialController extends Controller
{
    /**
     * Display the merged administrator credentials, user accounts, and roles management console.
     */
    public function index(Request $request): View
    {
        $user = Auth::user()->load('roles');
        $activeTab = $request->query('tab', 'credentials');

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
        $technicalCount = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['technical', 'technical-staff']);
        })->count();

        $recentUsers = User::with('roles')
            ->latest()
            ->take(6)
            ->get();

        $activeTab = $request->query('tab', 'overview');

        return view('admin.settings.credentials', [
            'user' => $user,
            'users' => $users,
            'roles' => $roles,
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'adminCount' => $adminCount,
            'staffCount' => $staffCount,
            'salesCount' => $salesCount,
            'technicalCount' => $technicalCount,
            'recentUsers' => $recentUsers,
            'search' => $search,
            'selectedRole' => $roleFilter,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Update the administrator's profile information (name & email).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('admin.settings.credentials')
            ->with('status', 'Your administrator profile credentials were successfully updated.');
    }

    /**
     * Update the administrator's account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.settings.credentials')
            ->with('status', 'Your password has been changed successfully.');
    }
}
