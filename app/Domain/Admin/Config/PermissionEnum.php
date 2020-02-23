<?php

namespace App\Domain\Admin\Config;

use App\Domain\Common\Enum\BaseEnum;

class PermissionEnum extends BaseEnum {
    const DASHBOARD = 'DASHBOARD'; // 主页
    const ADMIN_MANAGEMENT = 'ADMIN_MANAGEMENT'; // 管理员管理
    const ADMIN_VIEW = 'ADMIN_VIEW'; // 管理员列表
    const ADMIN_CREATE = 'ADMIN_CREATE'; // 添加管理员
    const ADMIN_UPDATE = 'ADMIN_UPDATE'; // 更新管理员
    const ADMIN_DELETE = 'ADMIN_DELETE'; // 删除管理员
    const ROLE_VIEW = 'ROLE_VIEW'; // 角色列表
    const ROLE_CREATE = 'ROLE_CREATE'; // 添加角色
    const ROLE_UPDATE = 'ROLE_UPDATE'; // 更新角色
    const ROLE_DELETE = 'ROLE_DELETE'; // 删除角色
}