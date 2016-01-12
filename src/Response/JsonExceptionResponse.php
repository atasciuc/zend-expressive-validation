<?php
namespace ExpressiveValidator\Response;

use Zend\Diactoros\Response\JsonResponse;

class JsonExceptionResponse extends JsonResponse
{
    /**
     * @param mixed $code
     * @param int $message
     */
    public function __construct($code, $message)
    {
        $responseData = [
            'code' => $code,
            'message' => $message,
            'reference' => '',
        ];
        parent::__construct($responseData, $code);
    }
}
