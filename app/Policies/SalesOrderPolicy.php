<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;
use App\Support\Workflow;

class SalesOrderPolicy
{
    /**
     * Determine whether the user can view any sales orders.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the sales order.
     */
    public function view(User $user, SalesOrder $salesOrder): bool
    {
        // Admins and Accounting see all orders
        if ($user->canViewAllOrders()) {
            return true;
        }

        // Filer can view
        if ($salesOrder->user_id === $user->id) {
            return true;
        }

        // User whose role is required for current step can view
        if (Workflow::canUserActOnStep($user, $salesOrder->current_step)) {
            return true;
        }

        // Users who acted on it previously can view
        return $salesOrder->logs()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create sales orders (ASO).
     */
    public function create(User $user): bool
    {
        return $user->isAdsAdmin() || $user->hasRole([Workflow::ROLE_SALES, 'sales-executive']);
    }

    /**
     * Determine whether the user can act on the current step of the sales order.
     * The server must block anyone acting on a step that is not theirs.
     */
    public function actOnStep(User $user, SalesOrder $salesOrder, ?string $step = null): bool
    {
        if (!$salesOrder->isInProgress()) {
            return false;
        }

        $targetStep = $step ?: $salesOrder->current_step;

        // Ensure the order is actually at this step
        if ($salesOrder->current_step !== $targetStep) {
            return false;
        }

        return Workflow::canUserActOnStep($user, $targetStep);
    }

    /**
     * Determine whether the user can view accounting and management reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->canViewReports();
    }

    /**
     * Determine whether the user can manage ADS workflow roles.
     */
    public function manageRoles(User $user): bool
    {
        return $user->isAdsAdmin();
    }
}
