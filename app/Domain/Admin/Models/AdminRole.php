<?php

namespace App\Domain\Admin\Models;

use App\Customize\Database\Eloquent\Model;

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
}
