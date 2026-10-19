<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Roles associated with this user.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Check if user has specific role(s) by slug.
     */
    public function hasRole(string|array $roles): bool
    {
        $roleList = (array) $roles;

        return $this->roles->pluck('slug')->intersect($roleList)->isNotEmpty();
    }

    /**
     * Determine if the user has an admin-level role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['it-admin', 'warehouse-admin', 'admin']);
    }

    /**
     * Determine if the user is an IT Administrator.
     */
    public function isItAdmin(): bool
    {
        return $this->hasRole('it-admin');
    }

    /**
     * Determine if the user has access to Admin Credentials.
     * Only IT Administrators have access; all other roles (Warehouse, Technical, Sales, etc.) are restricted.
     */
    public function canAccessAdminCredentials(): bool
    {
        return $this->isItAdmin();
    }

    /**
     * Determine if the user is a Technical personnel.
     */
    public function isTechnical(): bool
    {
        return $this->hasRole(['technical', 'technical-staff']);
    }

    /**
     * Determine if the user is a Warehouse Admin.
     */
    public function isWarehouseAdmin(): bool
    {
        return $this->hasRole(['warehouse-admin', 'admin']);
    }

    /**
     * Determine if the user is a Warehouse Staff.
     */
    public function isWarehouseStaff(): bool
    {
        return $this->hasRole(['warehouse-staff', 'staff']);
    }

    /**
     * Determine if the user is in Sales.
     */
    public function isSales(): bool
    {
        return $this->hasRole(['sales-executive', 'sales']);
    }

    /**
     * Determine if user should see full warehouse operations floor & SOP engine.
     * Only Warehouse Admin and Staff see the 5-stage warehouse operations engine.
     * Technical (Ariel Moro) and IT Admin do NOT see warehouse floor operations.
     */
    public function canViewWarehouseOperations(): bool
    {
        return $this->isWarehouseAdmin() || $this->isWarehouseStaff();
    }

    /**
     * Determine if user has permission to add, edit, or delete inventory.
     * Sales accounts have view-only access.
     */
    public function canManageInventory(): bool
    {
        return !$this->isSales();
    }

    /**
     * Determine if user has permission to create, verify, or process SRF workflow actions.
     * Sales accounts have view-only access to SRF summary and status.
     */
    public function canManageSrf(): bool
    {
        return !$this->isSales();
    }

    /**
     * Sales orders filed by this user.
     *
     * @return HasMany<SalesOrder, $this>
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Action logs executed by this user.
     *
     * @return HasMany<OrderLog, $this>
     */
    public function orderLogs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }

    /**
     * Determine if user has administrator privileges in ADS Sales Order System.
     */
    public function isAdsAdmin(): bool
    {
        return (bool) $this->is_admin || $this->hasRole(['admin', 'it-admin']);
    }

    /**
     * Determine if user can view all orders across all queues.
     */
    public function canViewAllOrders(): bool
    {
        return $this->isAdsAdmin() || $this->hasRole('accounting');
    }

    /**
     * Determine if user can view reports (Admins and Accounting only).
     */
    public function canViewReports(): bool
    {
        return $this->isAdsAdmin() || $this->hasRole('accounting');
    }

    /**
     * Determine if user is strictly restricted to ADS Sales Orders only.
     * Restricted users cannot view the warehouse floor, inventory catalog, or SRF requisitions.
     */
    public function canOnlyAccessSalesOrders(): bool
    {
        // Full warehouse staff, inventory supervisor, dispatch officer, and technical users have warehouse access
        if ($this->isWarehouseStaff() || $this->isTechnical() || $this->hasRole(['inventory-supervisor', 'dispatch-officer', 'warehouse-admin'])) {
            return false;
        }

        // If user is IT Admin / Super Admin, they have full system access unless explicitly in sales orders
        if ($this->isItAdmin() || (bool) $this->is_admin) {
            return false;
        }

        // All ADS workflow approvers & sales filers (Jovy, Fred, Owen, Macy, Badet, King, Sales, Content) only access Sales Orders
        if ($this->hasRole([
            \App\Support\Workflow::ROLE_JOVY,
            \App\Support\Workflow::ROLE_FRED,
            \App\Support\Workflow::ROLE_OWEN,
            \App\Support\Workflow::ROLE_MACY,
            \App\Support\Workflow::ROLE_BADET,
            \App\Support\Workflow::ROLE_KING,
            \App\Support\Workflow::ROLE_SALES,
            \App\Support\Workflow::ROLE_CONTENT,
            'sales',
            'sales-executive',
        ])) {
            return true;
        }

        $restrictedEmails = [
            'anne.liboon@globaltronics.net',
            'sheryl.quilla@globaltronics.net',
            'sherly.quilla@globaltronics.net',
            'jovy@globaltronics.net',
            'fred@globaltronics.net',
            'owen@globaltronics.net',
            'macy@globaltronics.net',
            'badet@globaltronics.net',
            'king@globaltronics.net',
            'content@globaltronics.net',
            'sales@globaltronics.net',
        ];

        return in_array(strtolower((string) $this->email), $restrictedEmails);
    }

    /**
     * Get the primary role for the user.
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}

