<?php

namespace App\Domain\Common\Helpers;

use App\Domain\Common\ErrorCode;
use Symfony\Component\HttpFoundation\Response as FResponse;
use Illuminate\Support\Facades\Response;

trait ApiResponse
{
    protected $statusCode;
    protected $statusText;
    protected $message;

    /**
     * @param int $statusCode
     * @return string
     */
    private function getStatusText(int $statusCode)
    {
        try {
            $text = FResponse::$statusTexts[$statusCode];
        } catch (\Exception $e) {
            $text = 'Undefined status code';
        }
        return $text;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    private function setStatus(int $statusCode)
    {
        $this->statusCode = $statusCode;
        $this->statusText = $this->getStatusText($statusCode);
        return $this;
    }

    /**
     * @param $data
     * @param array $header
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    private function respond($data, $header = [])
    {
        return $data ? Response::json($data, $this->statusCode, $header)
            : Response::noContent($this->statusCode, $header);
    }

    /**
     * @param string|array $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function success($data = [], int $statusCode = FResponse::HTTP_OK)
    {
        return $this->setStatus($statusCode)->respond($data);
    }

    /**
     * @param string|array $message
     * @param int $errorCode
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function failed($message = '', int $errorCode = ErrorCode::DEFAULT, int $statusCode = FResponse::HTTP_BAD_REQUEST)
    {
        return $this->setStatus($statusCode)->respond([
            'error_code' => $errorCode,
            'message' => $message ?: $this->statusText
        ]);
    }

    /**
     * Create data successfully
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function created($data = [])
    {
        return $this->success($data, FResponse::HTTP_CREATED);
    }

    /**
     * Received a request from the client, but has not started processing yet
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accepted()
    {
        return $this->success([], FResponse::HTTP_ACCEPTED);
    }

    /**
     * Deleted or updated successfully
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function noContent()
    {
        return $this->success([], FResponse::HTTP_NO_CONTENT);
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFound($message = '')
    {
        return $this->failed($message, ErrorCode::NOT_FOUND, FResponse::HTTP_NOT_FOUND);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function internalServerError()
    {
        return $this->failed('', ErrorCode::INTERNAL_SERVER_ERROR, FResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
