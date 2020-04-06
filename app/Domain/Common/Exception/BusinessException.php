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
     * @param int $code
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(int $code = ErrorCode::DEFAULT, string $message = '', int $statusCode = 400)
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
