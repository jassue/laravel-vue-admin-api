<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Admin\Config\StatusEnum;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\AdminResource;
use App\Http\Resources\RoleResource;
use Facades\App\Domain\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        $user = $request->user();
        $user->permissions = AdminService::getAllPermissionByRoles($user->roles);
        return $this->success(new AdminResource($user));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updateBySelf(Request $request)
    {
        $request->validate([
            'name'             => 'required|max:10',
            'old_password'     => 'nullable|required_with:password|min:6|max:30',
            'password'         => 'nullable|min:6|max:30',
            'confirm_password' => 'nullable|required_with:password|same:password'
        ]);
        return $this->success(AdminService::updateByAdmin($request->user(), $request->all()));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|exists:admins,id',
            'status' => 'required|integer|in:' . implode(',', StatusEnum::getConstants())
        ]);
        $ids = $request->input('ids');
        $status = $request->input('status');
        AdminService::toggleAdminStatusByIds($request->user(), $ids, $status);
        return $this->success([
            'status' => $status,
            'status_text' => StatusEnum::$statusMap[$status]
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getRoleListForCreateOrUpdate()
    {
        return $this->success(AdminService::getRoleListForCreateOrUpdate());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function detail(int $id)
    {
        return $this->success(new AdminResource(AdminService::getById($id)));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
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
        $admin = AdminService::getById($id);
        AdminService::update($request->user(), $admin, $request->all());
        AdminService::syncRole($admin, $request->input('role_ids'));
        AdminService::forgetPermissionCacheById($id);
        return $this->success($admin);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        AdminService::destroyByIds($request->user(), [$id]);
        return $this->noContent();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchDestroy(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|exists:admins,id'
        ]);
        $ids = $request->input('ids');
        AdminService::destroyByIds($request->user(), $ids);
        return $this->noContent();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getPermissionListForCreateOrUpdate()
    {
        return $this->success(AdminService::getPermissionTreeList());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function roleDetail(int $id)
    {
        return $this->success(new RoleResource(AdminService::getRoleById($id)));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
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
        $role = AdminService::getRoleById($id);
        AdminService::updateRole($request->user(), $role, $request->all());
        AdminService::roleSyncPermission($role, $request->input('permission_ids'));
        AdminService::forgetPermissionCacheByRoleIds([$id]);
        return $this->success($role);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleDestroy(Request $request, int $id)
    {
        AdminService::destroyRoleByIds($request->user(), [$id]);
        AdminService::forgetPermissionCacheByRoleIds([$id]);
        return $this->noContent();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleBatchDestroy(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|exists:admin_roles,id'
        ]);
        $ids = $request->input('ids');
        AdminService::destroyRoleByIds($request->user(), $ids);
        AdminService::forgetPermissionCacheByRoleIds($ids);
        return $this->noContent();
    }
}
