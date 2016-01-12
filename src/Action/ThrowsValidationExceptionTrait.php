<?php

namespace ExpressiveValidator\Action;

use ExpressiveValidator\Validator\ValidationFailedException;
use ExpressiveValidator\Validator\ValidationResultInterface;

trait ThrowsValidationExceptionTrait
{
    public function throwValidationExceptionWith(ValidationResultInterface $validationResult, $property, $message)
    {
            $validationResult->addInvalidMessage($property, [
                $message
            ]);
            $validationException = new ValidationFailedException();
            $validationException->setValidationResult($validationResult);
            throw $validationException;
    }
}
