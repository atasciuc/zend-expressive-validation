<?php

namespace ExpressiveValidator\Validator;

use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface as Router;
use Zend\Expressive\Router\RouterInterface;
use Zend\Stratigility\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ValidationMiddleware implements MiddlewareInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @codeCoverageIgnore
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates the request
     * @param Request $request
     * @param Response $response
     * @param callable|null $out
     * @inheritdoc
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        /**
         * @var ValidationResult $validationResult
         */
        $validationResult = $this->validator->validate($request);
        if (!is_bool($validationResult) && $validationResult->notValid()) {
            $validationException = new ValidationFailedException();
            $validationException->setValidationResult($validationResult);
            throw $validationException;
        } elseif (!is_bool($validationResult)) {    // Valid
            return $out($validationResult->getRequest(), $response);
        } else {
            return $out($request, $response);
        }
    }
}
