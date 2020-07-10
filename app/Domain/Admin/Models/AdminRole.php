<?php

namespace App\Domain\Admin\Models;

use App\Customize\Model;

class AdminRole extends Model
{
    protected $fillable = [
        'name', 'desc', 'is_preset'
    ];

    protected $hidden = [
        'updated_at', 'is_preset'
    ];

    protected $casts = [
        'is_preset' => 'boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissionRelation()
    {
        return $this->hasMany(RoleHasPermission::class, 'role_id', 'id')->select('role_id', 'permission_id');
    }
}
