<?php

namespace App\Domain\Admin\Models;

use App\Customize\User as Authenticatable;
use Facades\App\Domain\Admin\AdminService;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use Notifiable;

    const AUTH_CACHE_KEY_PRE = 'admin_auth_';

    protected $fillable = [
        'username', 'password', 'name', 'status', 'is_preset'
    ];

    protected $hidden = [
        'password', 'is_preset'
    ];

    protected $casts = [
        'is_preset' => 'boolean'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_has_roles', 'admin_id', 'role_id');
    }

    public function hasPermission(string $permissionKey)
    {
        $ownedPermissions = Cache::remember(self::AUTH_CACHE_KEY_PRE . $this->id, 24 * 60 * 60, function () {
            return AdminService::getAllPermissionByRoles($this->roles)->pluck('key');
        });
        return $ownedPermissions->contains($permissionKey);
    }
}
