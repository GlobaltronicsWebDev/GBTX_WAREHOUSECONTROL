<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalesOrderRoleController extends Controller
{
    /**
     * Admin-only screen to view & assign ADS workflow roles to users.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isAdsAdmin()) {
            abort(403, 'Unauthorized. Only administrators can access ADS workflow role configuration.');
        }

        $search = $request->query('search');
        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name', 'asc')->paginate(20)->withQueryString();
        $workflowRoles = Workflow::roles();

        // Get DB roles for workflow slugs
        $dbRoles = Role::whereIn('slug', array_keys($workflowRoles))->get()->keyBy('slug');

        return view('admin.sales-orders.roles', [
            'users' => $users,
            'workflowRoles' => $workflowRoles,
            'dbRoles' => $dbRoles,
            'search' => $search,
        ]);
    }

    /**
     * Update roles & admin flag for a user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isAdsAdmin()) {
            abort(403, 'Unauthorized. Only administrators can update ADS workflow roles.');
        }

        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $roleSlugs = $validated['roles'] ?? [];
        $dbRoles = Role::whereIn('slug', $roleSlugs)->pluck('id')->toArray();

        // Sync workflow roles while preserving system warehouse roles if any exist
        $existingNonWorkflowRoleIds = $user->roles()
            ->whereNotIn('slug', array_keys(Workflow::roles()))
            ->pluck('roles.id')
            ->toArray();

        $mergedRoleIds = array_unique(array_merge($existingNonWorkflowRoleIds, $dbRoles));
        $user->roles()->sync($mergedRoleIds);

        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return redirect()->route('admin.sales-orders.roles')
            ->with('status', "Roles and permissions for {$user->name} updated successfully.");
    }
}
