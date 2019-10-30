<?php

namespace App\Domain\Common;


class ErrorCode
{
    const DEFAULT = 40000;
    const UNAUTHORIZED = 40100;
    const FORBIDDEN = 40300;
    const NOT_FOUND = 40400;
    const UNPROCESSABLE_ENTITY = 42200;
    const INTERNAL_SERVER_ERROR = 50000;
}
