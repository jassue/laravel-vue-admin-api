<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use Facades\App\Domain\Admin\AdminService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validate($request,
            [
                'username' => 'required|min:3|max:16',
                'password' => 'required'
            ]
        );
        $admin = AdminService::getByUsername($request->input('username'));
        AdminService::checkLoginAuth($admin, $request->input('password'));
        return $this->success($this->_buildTokenOutput(JWTAuth::fromUser($admin)));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
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
