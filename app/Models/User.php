<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Permission slugs, loaded once per request. */
    protected ?Collection $permissionSlugCache = null;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'role',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function hasRole(string $role): bool
    {
        if ($this->role === 'admin' || $this->role === $role) {
            return true;
        }

        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->permissionSlugs()->contains($permission);
    }

    /**
     * Every permission slug this user holds, from their assigned roles.
     * A manager with no role assigned falls back to the default "manager" role,
     * so accounts created before roles were enforced keep working.
     *
     * @return Collection<int, string>
     */
    public function permissionSlugs(): Collection
    {
        if ($this->permissionSlugCache !== null) {
            return $this->permissionSlugCache;
        }

        $roles = $this->roles()->with('permissions')->get();

        if ($roles->isEmpty() && $this->role === 'manager') {
            $default = Role::with('permissions')->where('slug', 'manager')->first();
            $roles = $default ? collect([$default]) : $roles;
        }

        return $this->permissionSlugCache = $roles->pluck('permissions')->flatten()->pluck('slug')->unique()->values();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }
}
