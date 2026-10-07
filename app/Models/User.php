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

#[Fillable(['name', 'email', 'password'])]
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
     * Get the primary role for the user.
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
