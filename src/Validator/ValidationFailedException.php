<?php

namespace ExpressiveValidator\Validator;

use Exception;

class ValidationFailedException extends Exception
{
    /**
     * @var ValidationResultInterface
     */
    private $validationResult;
    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @inheritdoc
     */
    public function __construct($message = "", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ValidationResultInterface
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }

    /**
     * @param ValidationResultInterface $validationResult
     */
    public function setValidationResult($validationResult)
    {
        $this->validationResult = $validationResult;
    }
}
