<?php

use App\Domain\Admin\Config\PermissionEnum;
use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminRole;
use Illuminate\Support\Facades\DB;
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
        // 添加角色
        $role = AdminRole::create([
            'name' => 'admin',
            'desc' => '由系统自动创建，拥有最高权限。',
            'is_preset' => true
        ]);
        DB::table('role_has_permissions')->insert(
            PermissionEnum::getFlattenCollection()->map(function ($permission) use ($role) {
                $newItem['role_id'] = $role->id;
                $newItem['permission_id'] = $permission['id'];
                $newItem['created_at'] = time();
                $newItem['updated_at'] = time();
                return $newItem;
            })->toArray()
        );

        // 添加管理员
        $admin = Admin::create([
            'id' => '1',
            'username' => 'admin',
            'name' => 'admin',
            'password' => '123456',
            'is_preset' => true
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
