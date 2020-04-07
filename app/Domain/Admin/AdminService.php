<?php

namespace App\Domain\Admin;

use App\Domain\Admin\Config\StatusEnum;
use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminPermission;
use App\Domain\Admin\Models\AdminRole;
use App\Domain\Common\ErrorCode;
use App\Domain\Common\Exception\BusinessException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    /**
     * @param integer $id
     * @return void
     */
    public function getById(int $id)
    {
        return Admin::findOrFail($id);
    }

    /**
     * @param string $username
     * @return void
     */
    public function getByUsername(string $username)
    {
        return Admin::where('username', $username)->first();
    }

    /**
     * @param $admin
     * @param string $password
     * @throws BusinessException
     */
    public function checkLoginAuth($admin, string $password) {
        if (!$admin || !Hash::check($password, $admin->password)) {
            throw new BusinessException(ErrorCode::ACCOUNT_PWD_ERROR);
        }
        if ($admin->status == StatusEnum::DISABLE) {
            throw new BusinessException(ErrorCode::ACCOUNT_DISABLE);
        }
    }

    /**
     * @param Collection $roles
     * @return void
     */
    public function getAllPermissionByRoles(Collection $roles)
    {
        $permissions = collect([]);
        $roles->each(function ($role) use (&$permissions) {
            $diff = $role->permissions->diff($permissions);
            $permissions = $permissions->merge($diff);
        });
        return $permissions->pluck('name');
    }

    /**
     * @param Admin $admin
     * @param array $data
     * @return Admin
     * @throws BusinessException
     */
    public function updateByAdmin(Admin $admin, array $data)
    {
        $admin->name = $data['name'];
        if (!empty($data['password'])) {
            if (!Hash::check($data['old_password'], $admin->password)) {
                throw new BusinessException(ErrorCode::OLD_PWD_ERROR);
            }
            $admin->password = $data['password'];
        }
        $admin->save();
        return $admin;
    }

    /**
     * @param null|string $keywords
     * @param null|integer $status
     * @param integer $pageSize
     * @return void
     */
    public function getAdminList($keywords, $status, int $pageSize)
    {
        return Admin::with('roles')
            ->when(!is_null($keywords), function ($query) use ($keywords) {
                $query->where('name', 'like', "{$keywords}%")
                    ->orWhere('username', 'like', "{$keywords}%");
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($pageSize);
    }

    /**
     * @param Admin $admin
     * @param array $adminIds
     * @throws BusinessException
     */
    public function checkAdminOperateAuth(Admin $admin, array $adminIds) {
        if (in_array(Admin::value('id'), $adminIds) || in_array($admin->id, $adminIds)) {
            throw new BusinessException( ErrorCode::CANT_OPERATION_ADMIN);
        }
    }

    /**
     * @param Collection $selfRoleCollection
     * @param array $roleIds
     * @throws BusinessException
     */
    public function checkRoleOperateAuth(Collection $selfRoleCollection, array $roleIds) {
        if (in_array(AdminRole::value('id'), $roleIds) || $selfRoleCollection->pluck('id')->intersect($roleIds)->isNotEmpty()) {
            throw new BusinessException(ErrorCode::CANT_OPERATION_ROLE);
        }
    }

    /**
     * @param Admin $admin
     * @param array $ids
     * @param int $status
     * @throws BusinessException
     */
    public function toggleAdminStatusByIds(Admin $admin, array $ids, int $status)
    {
        $this->checkAdminOperateAuth($admin, $ids);
        Admin::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * @param Admin $admin
     * @param array $ids
     * @throws BusinessException
     */
    public function destroyByIds(Admin $admin, array $ids) {
        $this->checkAdminOperateAuth($admin, $ids);
        Admin::whereIn('id', $ids)->delete();
        DB::table('admin_has_roles')->whereIn('admin_id', $ids)->delete();
    }

    /**
     * @return void
     */
    public function getRoleListForCreateOrUpdate() {
        return AdminRole::select('id', 'name')->get();
    }

    /**
     * @param string $name
     * @param string $username
     * @param string $password
     * @param StatusEnum $status
     * @return void
     */
    public function create(string $name, string $username, string $password, StatusEnum $status)
    {
        return Admin::create([
            'name'     => $name,
            'username' => $username,
            'password' => $password,
            'status'   => $status->value
        ]);
    }

    /**
     * @param Admin $admin
     * @param array $roleIds
     * @return void
     */
    public function bindRole(Admin $admin, array $roleIds)
    {
        $admin->roles()->attach(
            $roleIds,
            [
                'created_at' => Carbon::now()->timestamp,
                'updated_at' => Carbon::now()->timestamp
            ]
        );
    }

    /**
     * @param Admin $admin
     * @param array $roleIds
     * @return void
     */
    public function syncRole(Admin $admin, array $roleIds)
    {
        $admin->roles()->sync(
            collect($roleIds)->keyBy(function ($item) {
                return $item;
            })->map(function () {
                $newItem['created_at'] = Carbon::now()->timestamp;
                $newItem['updated_at'] = Carbon::now()->timestamp;
                return $newItem;
            })->toArray()
        );
    }

    /**
     * @param Admin $user
     * @param Admin $admin
     * @param array $params
     * @throws BusinessException
     */
    public function update(Admin $user, Admin $admin, array $params)
    {
        $this->checkAdminOperateAuth($user, [$admin->id]);
        if (is_null($params['password'])) {
            unset($params['password']);
        }
        $admin->update($params);
    }

    /**
     * @param integer $id
     * @return void
     */
    public function getRoleById(int $id)
    {
        return AdminRole::findOrFail($id);
    }

    /**
     * @param null|string $keywords
     * @param integer $pageSize
     * @return void
     */
    public function getRoleList($keywords, int $pageSize)
    {
        return AdminRole::when(!is_null($keywords), function ($query) use ($keywords) {
            $query->where('name', 'like', "%{$keywords}%");
        })
        ->latest()
        ->paginate($pageSize);
    }

    /**
     * @return void
     */
    public function getPermissionTreeList()
    {
        return AdminPermission::with('children')->where('parent_id', 0)->get();
    }

    /**
     * @param string $name
     * @return void
     */
    public function createRole(string $name)
    {
        return AdminRole::create([
            'name'  => $name
        ]);
    }

    /**
     * @param AdminRole $role
     * @param array $permissionIds
     * @return void
     */
    public function roleBindPermission(AdminRole $role, array $permissionIds)
    {
        $role->permissions()->attach(
            $permissionIds,
            [
                'created_at' => time(),
                'updated_at' => time()
            ]
        );
    }

    /**
     * @param AdminRole $role
     * @param array $permissionIds
     * @return void
     */
    public function roleSyncPermission(AdminRole $role, array $permissionIds)
    {
        $role->permissions()->sync(
            collect($permissionIds)->keyBy(function ($item) {
                return $item;
            })->map(function () {
                $newItem['created_at'] = Carbon::now()->timestamp;
                $newItem['updated_at'] = Carbon::now()->timestamp;
                return $newItem;
            })->toArray()
        );
    }

    /**
     * @param Admin $admin
     * @param AdminRole $role
     * @param array $params
     * @throws BusinessException
     */
    public function updateRole(Admin $admin, AdminRole $role, array $params)
    {
        $this->checkRoleOperateAuth($admin->roles, [$role->id]);
        $role->update($params);
    }

    /**
     * @param Admin $admin
     * @param array $roleIds
     * @throws BusinessException
     */
    public function destroyRoleByIds(Admin $admin, array $roleIds)
    {
        $this->checkRoleOperateAuth($admin->roles, $roleIds);
        $this->checkRoleUsing($roleIds);
        AdminRole::whereIn('id', $roleIds)->delete();
        DB::table('role_has_permissions')->whereIn('role_id', $roleIds)->delete();
    }

    /**
     * @param integer $id
     * @return void
     */
    public function forgetPermissionCacheById(int $id)
    {
        Cache::forget(Admin::AUTH_CACHE_KEY_PRE . $id);
    }

    /**
     * @param array $ids
     * @return void
     */
    public function forgetPermissionCacheByRoleIds(array $ids)
    {
        DB::table('admin_has_roles')
        ->whereIn('role_id', $ids)
        ->select('admin_id')
        ->get()
        ->each(function ($item) {
            $this->forgetPermissionCacheById($item->admin_id);
        });
    }

    /**
     * @param array $roleIds
     * @throws BusinessException
     */
    public function checkRoleUsing(array $roleIds) {
        if (DB::table('admin_has_roles')->whereIn('role_id', $roleIds)->exists()) {
            throw new BusinessException(ErrorCode::CANT_DELETE_ROLE);
        }
    }
}
