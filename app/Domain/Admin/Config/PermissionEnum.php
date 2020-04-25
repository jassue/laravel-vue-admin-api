<?php

namespace App\Domain\Admin\Config;

use App\Domain\Common\Enum\BaseEnum;
use Illuminate\Support\Collection;

class PermissionEnum extends BaseEnum {
    const DASHBOARD = 'DASHBOARD';
    const ADMIN_MANAGEMENT = 'ADMIN_MANAGEMENT';
    const ADMIN_VIEW = 'ADMIN_VIEW';
    const ADMIN_CREATE = 'ADMIN_CREATE';
    const ADMIN_UPDATE = 'ADMIN_UPDATE';
    const ADMIN_DELETE = 'ADMIN_DELETE';
    const ROLE_VIEW = 'ROLE_VIEW';
    const ROLE_CREATE = 'ROLE_CREATE';
    const ROLE_UPDATE = 'ROLE_UPDATE';
    const ROLE_DELETE = 'ROLE_DELETE';

    public static $permissionList = [
        [
            'id' => 1,
            'key' => self::DASHBOARD,
            'name' => '主页'
        ],
        [
            'id' => 2,
            'key' => self::ADMIN_MANAGEMENT,
            'name' => '管理员管理',
            'children' => [
                [
                    'id' => 3,
                    'key' => self::ADMIN_VIEW,
                    'name' => '管理员列表'
                ],
                [
                    'id' => 4,
                    'key' => self::ADMIN_CREATE,
                    'name' => '添加管理员'
                ],
                [
                    'id' => 5,
                    'key' => self::ADMIN_UPDATE,
                    'name' => '更新管理员'
                ],
                [
                    'id' => 6,
                    'key' => self::ADMIN_DELETE,
                    'name' => '删除管理员'
                ],
                [
                    'id' => 7,
                    'key' => self::ROLE_VIEW,
                    'name' => '角色列表'
                ],
                [
                    'id' => 8,
                    'key' => self::ROLE_CREATE,
                    'name' => '添加角色'
                ],
                [
                    'id' => 9,
                    'key' => self::ROLE_UPDATE,
                    'name' => '更新角色'
                ],
                [
                    'id' => 10,
                    'key' => self::ROLE_DELETE,
                    'name' => '删除角色'
                ]
            ]
        ]
    ];

    /**
     * 权限列表(二维数组)展开为一维数组
     * @return \Illuminate\Support\Collection
     */
    public static function getFlattenCollection() {
        $top = collect(self::$permissionList)
            ->map(function ($item) {
                if (isset($item['children']))
                    unset($item['children']);
                return $item;
            });
        $children = collect(self::$permissionList)
            ->flatten(2)
            ->filter(function ($item) {
                return is_array($item);
            });
        return $top->merge($children);
    }

    /**
     * @param Collection $ids
     * @return Collection
     */
    public static function getByIds(Collection $ids) {
        return self::getFlattenCollection()->whereIn('id', $ids);
    }
}
