<?php

namespace App\Domain\Common;


class ErrorCode
{
    const DEFAULT = 40000;
    const UNAUTHORIZED = 40100;
    const FORBIDDEN = 40300;
    const NOT_FOUND = 40400;
    const MODEL_NOT_FOUND = 40401;
    const HTTP_NOT_FOUND = 40402;
    const METHOD_NOT_ALLOWED = 40500;
    const UNPROCESSABLE_ENTITY = 42200;
    const INTERNAL_SERVER_ERROR = 50000;
    const SQL_ERROR = 60000;
    const OLD_PWD_ERROR = 60001;
    const CANT_OPERATION_ADMIN = 60002;
    const CANT_OPERATION_ROLE = 60003;
    const CANT_DELETE_ROLE = 60004;
    const ACCOUNT_PWD_ERROR = 60005;
    const ACCOUNT_DISABLE = 60006;

    const ErrorMsg = [
        self::DEFAULT => 'Default error.',
        self::UNAUTHORIZED => 'Unauthenticated.',
        self::FORBIDDEN => 'This action is unauthorized.',
        self::NOT_FOUND => 'Not Found',
        self::MODEL_NOT_FOUND => 'Model Not Found',
        self::HTTP_NOT_FOUND => 'HTTP Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::SQL_ERROR => 'Query error.',
        self::OLD_PWD_ERROR => '旧密码不正确',
        self::CANT_OPERATION_ADMIN => '无法对自己或内置管理员进行操作',
        self::CANT_OPERATION_ROLE => '无法对自身所属的角色或内置角色进行操作',
        self::CANT_DELETE_ROLE => '角色已关联账号，无法删除，请先解除关联',
        self::ACCOUNT_PWD_ERROR => '账号密码错误',
        self::ACCOUNT_DISABLE => '账号被禁用'
    ];
}
