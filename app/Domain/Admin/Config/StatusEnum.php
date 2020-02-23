<?php

namespace App\Domain\Admin\Config;

use App\Domain\Common\Enum\BaseEnum;

class StatusEnum extends BaseEnum
{
    const ENABLE = 0;
    const DISABLE = 1;

    public static $statusMap = [
      self::ENABLE => '启用',
      self::DISABLE => '禁用'
    ];
}