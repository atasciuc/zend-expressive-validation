<?php
namespace ExpressiveValidator\Exception;

use Exception;

/**
 * Used for methods which are not allowed
 * Class MethodNotAllowedException
 */
class MethodNotAllowedException extends Exception
{
    /**
     * @codeCoverageIgnore
     * @param string $message
     * @param Exception|null $previous
     */
    public function __construct($message = "This method is not allowed", Exception $previous = null)
    {
        parent::__construct($message, 405, $previous);
    }
}
