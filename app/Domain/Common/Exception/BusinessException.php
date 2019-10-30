<?php

namespace App\Domain\Common\Exception;

use Exception;
use App\Domain\Common\ErrorCode;

class BusinessException extends Exception
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    private $statusCode;

    /**
     * BusinessException constructor.
     * @param string|array $message
     * @param int $code
     * @param int $statusCode
     */
    public function __construct($message = '', int $code = ErrorCode::DEFAULT, int $statusCode = 400)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, null);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
