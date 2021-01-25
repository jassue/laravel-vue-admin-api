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
        AuthenticationException::class => ErrorCode::UNAUTHORIZED,
        ModelNotFoundException::class => ErrorCode::MODEL_NOT_FOUND,
        AuthorizationException::class => ErrorCode::FORBIDDEN,
        ValidationException::class => ErrorCode::UNPROCESSABLE_ENTITY,
        UnauthorizedHttpException::class => ErrorCode::UNAUTHORIZED,
        NotFoundHttpException::class => ErrorCode::HTTP_NOT_FOUND,
        MethodNotAllowedHttpException::class => ErrorCode::METHOD_NOT_ALLOWED,
        QueryException::class => ErrorCode::SQL_ERROR,
        BusinessException::class => ErrorCode::DEFAULT,
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
        $errorMsg = ErrorCode::ErrorMsg[$this->doReport[$this->report]] ?? $this->exception->getMessage();
        if ($this->exception instanceof ValidationException) {
            return $this->failed(
                collect($this->exception->errors())->first()[0],
                $this->doReport[$this->report]
            );
        }
        if ($this->exception instanceof BusinessException) {
            return $this->failed(
                $this->exception->getMessage() ?: (ErrorCode::ErrorMsg[$this->exception->getCode()] ?? ''),
                $this->exception->getCode()
            );
        }
        if ($this->exception instanceof QueryException) {
            if (!env('APP_DEBUG')) {
                return $this->failed(
                    $errorMsg,
                    $this->doReport[$this->report]
                );
            }
        }
        return $this->failed(
            $errorMsg,
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
