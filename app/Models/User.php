<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        // Aplicar el scope global de tenant
        static::addGlobalScope(new TenantScope());
        
        // Al crear un usuario, asignar automáticamente el tenant actual
        static::creating(function ($user) {
            if (!$user->tenant_id && app()->has('tenant')) {
                $user->tenant_id = tenant()->id;
            }
        });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'health_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

        /**
     * Permisos del usuario
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('granted_by')
            ->withTimestamps();
    }

    /**
     * Verificar si el usuario tiene un permiso
     */
    public function hasPermission(string $permissionName): bool
    {
        // Admin siempre tiene todos los permisos
        if ($this->role === 'admin') {
            return true;
        }

        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->permissions()->whereIn('name', $permissionNames)->exists();
    }

    /**
     * Verificar si el usuario tiene todos los permisos
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        $userPermissions = $this->permissions()->pluck('name')->toArray();
        return empty(array_diff($permissionNames, $userPermissions));
    }

    /**
     * Otorgar permiso al usuario
     */
    public function grantPermission(string|Permission $permission, ?User $grantedBy = null): void
    {
        $permissionId = $permission instanceof Permission 
            ? $permission->id 
            : Permission::where('name', $permission)->firstOrFail()->id;

        $this->permissions()->syncWithoutDetaching([
            $permissionId => ['granted_by' => $grantedBy?->id]
        ]);
    }

    /**
     * Revocar permiso al usuario
     */
    public function revokePermission(string|Permission $permission): void
    {
        $permissionId = $permission instanceof Permission 
            ? $permission->id 
            : Permission::where('name', $permission)->firstOrFail()->id;

        $this->permissions()->detach($permissionId);
    }

    /**
     * Sincronizar permisos del usuario
     */
    public function syncPermissions(array $permissionNames, ?User $grantedBy = null): void
    {
        $permissions = Permission::whereIn('name', $permissionNames)->get();
        
        $syncData = [];
        foreach ($permissions as $permission) {
            $syncData[$permission->id] = ['granted_by' => $grantedBy?->id];
        }

        $this->permissions()->sync($syncData);
    }
}
