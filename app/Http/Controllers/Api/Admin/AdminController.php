<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Admin\Config\StatusEnum;
use App\Domain\Common\ErrorCode;
use App\Domain\Common\Exception\BusinessException;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\AdminResource;
use App\Http\Resources\RoleResource;
use Facades\App\Domain\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends BaseController
{
    /**
     * @param Request $request
     * @return void
     */
    public function info(Request $request)
    {
        $user = $request->user();
        $user->permissions = AdminService::getAllPermissionByRoles($user->roles);
        return $this->success(new AdminResource($user));
    }

    /**
     * @param Request $request
     * @return void
     */
    public function updateBySelf(Request $request)
    {
        $request->validate([
            'name'             => 'required|max:10',
            'old_password'     => 'nullable|required_with:password|min:6|max:30',
            'password'         => 'nullable|min:6|max:30',
            'confirm_password' => 'nullable|required_with:password|same:password'
        ]);
        $admin = $request->user();
        if (!empty($request->input('password')) && !Hash::check($request->input('old_password'), $admin->password)) {
            throw new BusinessException('旧密码不正确', ErrorCode::OLD_PWD_ERROR);
        }
        return $this->success(AdminService::updateByAdmin($admin, $request->all()));
    }

    /**
     * @param Request $request
     * @return void
     */
    public function getList(Request $request)
    {
        $request->validate([
            'keyword'   => 'nullable|string',
            'limit'     => 'integer',
            'status'    => 'nullable|integer|in:' . implode(',', StatusEnum::getConstants()),
            'page'      => 'required|integer|min:1'
        ]);
        $params = $request->all();
        $data = AdminService::getAdminList($params['keyword'], $params['status'], $params['limit']);
        return AdminResource::collection($data);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|exists:admins,id',
            'status' => 'required|integer|in:' . implode(',', StatusEnum::getConstants())
        ]);
        $ids = $request->input('ids');
        $status = $request->input('status');
        if (in_array(1, $ids) || in_array($request->user()->id, $ids)) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        AdminService::toggleAdminStatusByIds($ids, $status);
        return $this->success([
            'status' => $status,
            'status_text' => StatusEnum::$statusMap[$status]
        ]);
    }

    /**
     * @return void
     */
    public function getRoleListForCreateOrUpdate()
    {
        return $this->success(AdminService::getRoleListForCreateOrUpdate());
    }

    /**
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:10|unique:admins',
            'username' => 'required|string|min:3|max:16|unique:admins',
            'role_ids' => 'required|array|exists:admin_roles,id',
            'password' => 'required|min:6|max:30',
            'status'   => 'required|integer|in:' . implode(',', StatusEnum::getConstants())
        ]);
        $admin = AdminService::create(
            $request->input('name'),
            $request->input('username'),
            $request->input('password'),
            StatusEnum::byValue($request->input('status'))
        );
        AdminService::bindRole($admin, $request->input('role_ids'));
        return $this->noContent();
    }

    /**
     * @param integer $id
     * @return void
     */
    public function detail(int $id)
    {
        return $this->success(new AdminResource(AdminService::getById($id)));
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return void
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'     => [
                'required',
                'string',
                'max:10',
                Rule::unique('admins')->ignore($id)
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:16',
                Rule::unique('admins')->ignore($id)
            ],
            'role_ids' => 'required|array|exists:admin_roles,id',
            'password' => 'nullable|min:6|max:30',
            'status'   => 'required|integer|in:' . implode(',', StatusEnum::getConstants())
        ]);
        if ($request->user()->id == $id || $id == 1) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        $admin = AdminService::getById($id);
        AdminService::update($admin, $request->all());
        AdminService::syncRole($admin, $request->input('role_ids'));
        AdminService::forgetPermissionCacheById($id);
        return $this->success($admin);
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return void
     */
    public function destroy(Request $request, int $id)
    {
        if ($id == 1 || $request->user()->id == $id) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        AdminService::destroyByIds([$id]);
        return $this->noContent();
    }

    /**
     * @param Request $request
     * @return void
     */
    public function batchDestroy(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|exists:admins,id'
        ]);
        $ids = $request->input('ids');
        if (in_array(1, $ids) || in_array($request->user()->id, $ids)) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        AdminService::destroyByIds($ids);
        return $this->noContent();
    }

    /**
     * @param Request $request
     * @return void
     */
    public function getRoleList(Request $request)
    {
        $request->validate([
            'keyword'   => 'nullable|string',
            'limit'     => 'integer',
            'page'      => 'required|integer|min:1'
        ]);
        $params = $request->all();
        $data = AdminService::getRoleList($params['keyword'], $params['limit']);
        return RoleResource::collection($data);
    }

    /**
     * @return void
     */
    public function getPermissionListForCreateOrUpdate()
    {
        return $this->success(AdminService::getPermissionTreeList());
    }

    /**
     * @param Request $request
     * @return void
     */
    public function roleStore(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:20|unique:admin_roles',
            'permission_ids' => 'required|array|exists:admin_permissions,id'
        ]);
        $role = AdminService::createRole($request->input('name'));
        AdminService::roleBindPermission($role, $request->input('permission_ids'));
        return $this->noContent();
    }

    /**
     * @param integer $id
     * @return void
     */
    public function roleDetail(int $id)
    {
        return $this->success(new RoleResource(AdminService::getRoleById($id)));
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return void
     */
    public function roleUpdate(Request $request, int $id)
    {
        $request->validate([
            'name'           => [
                'required',
                'string',
                'max:20',
                Rule::unique('admin_roles')->ignore($id)
            ],
            'permission_ids' => 'required|array|exists:admin_permissions,id'
        ]);
        if ($id == 1) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        $role = AdminService::getRoleById($id);
        AdminService::updateRole($role, $request->all());
        AdminService::roleSyncPermission($role, $request->input('permission_ids'));
        AdminService::forgetPermissionCacheByRoleIds([$id]);
        return $this->success($role);
    }

    /**
     * @param integer $id
     * @return void
     */
    public function roleDestroy(int $id)
    {
        if ($id == 1) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        if (AdminService::checkRoleUsing([$id])) {
            throw new BusinessException('角色已关联账号，无法删除，请先解除关联');
        }
        AdminService::destroyRoleByIds([$id]);
        AdminService::forgetPermissionCacheByRoleIds([$id]);
        return $this->noContent();
    }

    /**
     * @param Request $request
     * @return void
     */
    public function roleBatchDestroy(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|exists:admin_roles,id'
        ]);
        $ids = $request->input('ids');
        if (in_array(1, $ids)) {
            throw new BusinessException('无法操作', ErrorCode::CANT_OPERATION);
        }
        if (AdminService::checkRoleUsing($ids)) {
            throw new BusinessException('角色已关联账号，无法删除，请先解除关联');
        }
        AdminService::destroyRoleByIds($ids);
        AdminService::forgetPermissionCacheByRoleIds($ids);
        return $this->noContent();
    }
}
