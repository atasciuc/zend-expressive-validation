<?php

namespace ExpressiveValidator\Validator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ValidationRulesInterface
{
    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request);

    /**
     * Should return a class mapping
     * of the validations
     * @return  [] mixed
     */
    public function getValidationRules();

    /**
     * Should return the error messages
     * @return [] mixed
     */
    public function getMessages();
}
