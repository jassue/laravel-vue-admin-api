<?php

namespace App\Domain\Admin\Models;

use App\Customize\Model;

class AdminRole extends Model
{
    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'updated_at', 'pivot'
    ];

    public function permissions()
    {
        return $this->belongsToMany(AdminPermission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }
}
