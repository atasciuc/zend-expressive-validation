<?php
namespace ExpressiveValidator\Test\Validator;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\OptionsExtractor;
use ExpressiveValidator\Validator\RequestValidator;
use ExpressiveValidator\Validator\ValidationMiddleware;
use ExpressiveValidator\Validator\Validator;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Stratigility\Http\Response;
use Psr\Http\Message\ResponseInterface;
use ExpressiveValidator\Validator\ValidationFailedException;
use Psr\Http\Message\ServerRequestInterface;

class ValidationMiddlewareTest extends PHPUnit_Framework_TestCase
{
    use ValidatorProviderTrait;

    /**
     * @covers StdLib\Validator\ValidationMiddleware::__invoke
     */
    public function testInvokeMiddleware()
    {
        $this->applyValidationConfig();
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $validator = new Validator($optionExtractor, $this->router, $this->getEntityManagerMock());
        $middleware = new ValidationMiddleware($validator);
        $request = $this->createRequest(static::$urlValid);
        $response = $this->getMock(ResponseInterface::class);

        $closure = function ($requestIn, $responseIn, $out = null) use ($request, $response){
            $this->assertInstanceOf(ServerRequestInterface::class, $request);
            $this->assertInstanceOf(ServerRequestInterface::class, $requestIn);
            $this->assertArrayHasKey('agreement_id', $requestIn->getParsedBody());
            $this->assertInstanceOf(RequestValidator::class, $requestIn);
        };
        $middleware->__invoke($request, $response, $closure);
    }
    /**
     * Test throwing of the exception
     * @covers StdLib\Validator\ValidationMiddleware::__invoke
     */
    public function testInvokeMiddlewareThrowsException()
    {

        $this->setExpectedException(ValidationFailedException::class);

        $router = $this->getMock(FastRouteRouter::class);

        $this->applyValidationConfig();

        $router->expects($this->any())
        ->method('match')
        ->willReturn(RouteResult::fromRouteMatch($this->config[0]['path'],
            function(){},
            []));
        $optionExtractor = new OptionsExtractor($this->config, $router);
        $validator = new Validator($optionExtractor, $router, $this->getEntityManagerMock());
        $middleware = new ValidationMiddleware($validator);
        $request = $this->createRequest(static::$urlInvalid);
        $response = $this->getMock(ResponseInterface::class);
        $closure = function (){};
        $middleware->__invoke($request, $response, $closure);
    }

    /**
     * Test no validation attached
     * @covers StdLib\Validator\ValidationMiddleware::__invoke
     */
    public function testConstructor()
    {
        // Apply the route configuration
        $this->applyValidationConfig();

        //Remove the validation options
        $this->config[0]['options'] =  [];

        $optionExtractor = new OptionsExtractor($this->config, $this->router);

        $validator = new Validator($optionExtractor, $this->router, $this->getEntityManagerMock());

        $validationMiddleware = new ValidationMiddleware($validator);

        $request = $this->createRequest(static::$urlInvalid);
        $response = $this->getMock(ResponseInterface::class);
        $validationMiddleware->__invoke($request, $response, function ($requestIn, $responseIn) use ($request, $response) {
            $this->assertEquals($requestIn, $request);
            $this->assertEquals($responseIn, $response);
        });
    }
}
