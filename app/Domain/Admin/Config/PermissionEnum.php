<?php

namespace App\Domain\Admin\Config;

use App\Domain\Common\Enum\BaseEnum;
use Illuminate\Support\Collection;

class PermissionEnum extends BaseEnum {
    const DASHBOARD = 'DASHBOARD';
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
            'key' => '',
            'name' => '系统管理',
            'children' => [
                [
                    'id' => 3,
                    'key' => '',
                    'name' => '管理员管理',
                    'children' => [
                        [
                            'id' => 4,
                            'key' => self::ADMIN_VIEW,
                            'name' => '列表'
                        ],
                        [
                            'id' => 5,
                            'key' => self::ADMIN_CREATE,
                            'name' => '添加'
                        ],
                        [
                            'id' => 6,
                            'key' => self::ADMIN_UPDATE,
                            'name' => '编辑'
                        ],
                        [
                            'id' => 7,
                            'key' => self::ADMIN_DELETE,
                            'name' => '删除'
                        ]
                    ]
                ],
                [
                    'id' => 8,
                    'key' => '',
                    'name' => '角色管理',
                    'children' => [
                        [
                            'id' => 9,
                            'key' => self::ROLE_VIEW,
                            'name' => '列表'
                        ],
                        [
                            'id' => 10,
                            'key' => self::ROLE_CREATE,
                            'name' => '添加'
                        ],
                        [
                            'id' => 11,
                            'key' => self::ROLE_UPDATE,
                            'name' => '更新'
                        ],
                        [
                            'id' => 12,
                            'key' => self::ROLE_DELETE,
                            'name' => '删除'
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * 权限列表(多维数组)展开为一维数组
     * @return Collection
     */
    public static function getFlattenCollection() :Collection
    {
        return collect(self::treeToList(self::$permissionList));
    }

    /**
     * @param array $data
     * @return array
     */
    public static function treeToList(array $data) :array
    {
        $tree = [];
        foreach ($data as $item) {
            if (isset($item['children'])) {
                $childList = self::treeToList($item['children']);
                unset($item['children']);
                $tree = array_merge($tree, $childList);
            }
            !empty($item['key']) && $tree[] = $item;
        }
        return $tree;
    }

    /**
     * @param Collection $ids
     * @return Collection
     */
    public static function getByIds(Collection $ids) :Collection
    {
        return self::getFlattenCollection()->whereIn('id', $ids);
    }
}
