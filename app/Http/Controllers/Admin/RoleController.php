<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of system and custom roles in the merged Admin Credentials hub.
     */
    public function index(Request $request): View
    {
        $user = Auth::user()->load('roles');
        $activeTab = 'roles';

        $usersQuery = User::with('roles')->latest();
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
            'recentUsers' => $recentUsers,
            'search' => null,
            'selectedRole' => null,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'badge_color' => ['required', 'string', 'in:purple,amber,emerald,sky,indigo,blue,rose,teal'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'],
            'badge_color' => $validated['badge_color'],
            'is_system' => false,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('status', "Role '{$role->name}' was created successfully.");
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'badge_color' => ['required', 'string', 'in:purple,amber,emerald,sky,indigo,blue,rose,teal'],
        ]);

        $role->name = $validated['name'];
        $role->description = $validated['description'];
        $role->badge_color = $validated['badge_color'];

        if (! $role->is_system) {
            $baseSlug = Str::slug($validated['name']);
            if ($baseSlug !== $role->slug && ! Role::where('slug', $baseSlug)->where('id', '!=', $role->id)->exists()) {
                $role->slug = $baseSlug;
            }
        }

        $role->save();

        return redirect()->route('admin.roles.index')
            ->with('status', "Role '{$role->name}' was updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['error' => "System role '{$role->name}' is protected and cannot be deleted."]);
        }

        $assignedCount = $role->users()->count();
        if ($assignedCount > 0) {
            return back()->withErrors(['error' => "Cannot delete role '{$role->name}' because {$assignedCount} user account(s) are currently assigned to it. Reassign these users before deleting."]);
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('status', "Role '{$roleName}' was deleted successfully.");
    }
}
