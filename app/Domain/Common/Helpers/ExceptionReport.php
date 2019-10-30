<?php

namespace App\Domain\Common\Helpers;

use App\Domain\Common\ErrorCode;
use App\Domain\Common\Exception\BusinessException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ExceptionReport
{
    use ApiResponse;

    public $exception;
    public $request;
    public $report;

    public $doReport = [
        AuthenticationException::class => 401,
        ModelNotFoundException::class => 404,
        AuthorizationException::class => 403,
        ValidationException::class => 422,
        UnauthorizedHttpException::class => 401,
        NotFoundHttpException::class => 404,
        MethodNotAllowedHttpException::class => 405,
        QueryException::class => 401,
        BusinessException::class => 400,
    ];

    /**
     * ExceptionReport constructor.
     * @param Request $request
     * @param Exception $exception
     */
    private function __construct(Request $request, Exception $exception)
    {
        $this->exception = $exception;
        $this->request = $request;
    }

    /**
     * @param $className
     * @param callable $callable
     */
    public function register($className, callable $callable)
    {
        $this->doReport[$className] = $callable;
    }

    /**
     * @return bool
     */
    public function shouldReturn()
    {
        foreach (array_keys($this->doReport) as $report) {
            if ($this->exception instanceof $report) {
                $this->report = $report;
                return true;
            }
        }
        return false;
    }

    /**
     * @param Exception $e
     * @return ExceptionReport
     */
    public static function make(Exception $e)
    {
        return new static(\request(), $e);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function report(){
        if ($this->exception instanceof ValidationException) {
            return $this->failed(
                collect($this->exception->errors())->first()[0],
                ErrorCode::UNPROCESSABLE_ENTITY,
                $this->exception->status
            );
        }
        if ($this->exception instanceof BusinessException) {
            return $this->failed(
                $this->exception->getMessage(),
                $this->exception->getCode(),
                $this->exception->getStatusCode()
            );
        }
        return $this->failed(
            $this->exception->getMessage(),
            ErrorCode::DEFAULT,
            $this->doReport[$this->report]
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function prodReport(){
        return $this->internalServerError();
    }
}
