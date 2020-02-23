<?php

use App\Domain\Admin\Models\AdminPermission;
use App\Domain\Admin\Config\PermissionEnum;
use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminRole;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 添加权限
        AdminPermission::insert([
            [
                'id' => 1,
                'parent_id' => 0,
                'name' => PermissionEnum::DASHBOARD,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 2,
                'parent_id' => 0,
                'name' => PermissionEnum::ADMIN_MANAGEMENT,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 3,
                'parent_id' => 2,
                'name' => PermissionEnum::ADMIN_VIEW,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 4,
                'parent_id' => 2,
                'name' => PermissionEnum::ADMIN_CREATE,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 5,
                'parent_id' => 2,
                'name' => PermissionEnum::ADMIN_UPDATE,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 6,
                'parent_id' => 2,
                'name' => PermissionEnum::ADMIN_DELETE,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 7,
                'parent_id' => 2,
                'name' => PermissionEnum::ROLE_VIEW,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 8,
                'parent_id' => 2,
                'name' => PermissionEnum::ROLE_CREATE,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 9,
                'parent_id' => 2,
                'name' => PermissionEnum::ROLE_UPDATE,
                'created_at' => time(),
                'updated_at' => time()
            ],
            [
                'id' => 10,
                'parent_id' => 2,
                'name' => PermissionEnum::ROLE_DELETE,
                'created_at' => time(),
                'updated_at' => time()
            ]
        ]);

        // 添加角色
        $role = AdminRole::create([
            'name' => 'admin',
        ]);
        $role->permissions()->attach(
            AdminPermission::all()->pluck('id'),
            [
                'created_at' => time(),
                'updated_at' => time()
            ]
        );

        // 添加管理员
        $admin = Admin::create([
            'id' => '1',
            'username' => 'admin',
            'name' => 'admin',
            'password' => '123456'
        ]);
        $admin->roles()->attach(
            $role->id,
            [
                'created_at' => time(),
                'updated_at' => time()
            ]
        );
    }
}
