<?php

namespace App\Domain\Admin;

use App\Domain\Admin\Config\StatusEnum;
use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminPermission;
use App\Domain\Admin\Models\AdminRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
     * @return void
     */
    public function updateByAdmin(Admin $admin, array $data)
    {
        $admin->name = $data['name'];
        if (!empty($data['password'])) {
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
                $query->where('name', 'like', "%{$keywords}%");
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($pageSize);
    }

    /**
     * @param array $ids
     * @param integer $status
     * @return void
     */
    public function toggleAdminStatusByIds(array $ids, int $status)
    {
        Admin::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * @param array $ids
     * @return void
     */
    public function destroyByIds(array $ids) {
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
     * @param Admin $admin
     * @param array $params
     * @return void
     */
    public function update(Admin $admin, array $params)
    {
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
     * @param AdminRole $role
     * @param array $params
     * @return void
     */
    public function updateRole(AdminRole $role, array $params)
    {
        $role->update($params);
    }

    /**
     * @param array $roleIds
     * @return void
     */
    public function destroyRoleByIds(array $roleIds)
    {
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
     * @return void
     */
    public function checkRoleUsing(array $roleIds) {
        return DB::table('admin_has_roles')->whereIn('role_id', $roleIds)->exists();
    }
}