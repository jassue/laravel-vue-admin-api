<?php

namespace App\Domain\Admin\Models;

use App\Customize\Model;

class AdminPermission extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'permission_name'
    ];

    public function children()
    {
        return $this->hasMany(AdminPermission::class, 'parent_id', 'id');
    }
}
