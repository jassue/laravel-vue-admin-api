<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Admin\Config\StatusEnum;
use App\Domain\Common\ErrorCode;
use App\Domain\Common\Exception\BusinessException;
use App\Http\Controllers\Api\BaseController;
use Facades\App\Domain\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        $this->validate($request,
            [
                'username' => 'required|min:3|max:16',
                'password' => 'required'
            ]
        );
        $username = $request->input('username');
        $password = $request->input('password');
        $admin = AdminService::getByUsername($username);
        if (!$admin || !Hash::check($password, $admin->password)) {
            throw new BusinessException('账号密码错误', ErrorCode::ACCOUNT_ERROR);
        }
        if ($admin->status == StatusEnum::DISABLE) {
            throw new BusinessException('账号被禁用', ErrorCode::ACCOUNT_DISABLE);
        }
        return $this->success($this->_buildTokenOutput(JWTAuth::fromUser($admin)));
    }

    /**
     * @return void
     */
    public function logout()
    {
        auth()->logout();
        return $this->noContent();
    }

    /**
     * @param string $token
     * @return array
     */
    private function _buildTokenOutput(string $token) {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60
        ];
    }
}
