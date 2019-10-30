<?php

namespace App\Domain\Common\Enum;


class StatusEnum extends BaseEnum
{
    const DISABLE = 0;
    const ENABLE = 1;

    /**
     * @var array
     */
    protected static $statusMap = [
        self::DISABLE => '禁用',
        self::ENABLE => '启用'
    ];
}
