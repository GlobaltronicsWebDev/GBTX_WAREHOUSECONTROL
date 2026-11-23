<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'username', 'department_id', 'company_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
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
     * The user's assigned department.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * The user's associated company.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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

    public function canDeleteInventoryItems(): bool
    {
        return $this->isAdmin();
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
     * Only IT Administrators have access; all other roles are restricted.
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
     * Determine if user should see full warehouse operations floor & SOP engine.
     * Only Warehouse Admin and Staff see the 5-stage warehouse operations engine.
     * Technical (Ariel Moro) and IT Admin do NOT see warehouse floor operations.
     */
    public function canViewWarehouseOperations(): bool
    {
        return $this->isWarehouseAdmin() || $this->isWarehouseStaff();
    }

    /**
     * Determine if user has permission to create, verify, or process SRF workflow actions.
     */
    public function canManageSrf(): bool
    {
        return $this->isItAdmin() || $this->isTechnical() || $this->canViewWarehouseOperations();
    }

    /**
     * Get the primary role for the user.
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
