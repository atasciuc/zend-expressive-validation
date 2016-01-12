<?php

namespace ExpressiveValidator\Exception;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct($message = "Entity not found", $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
