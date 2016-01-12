<?php
namespace ExpressiveValidator\Test\Middleware;


use Exception;
use ExpressiveValidator\Exception\EntityNotFoundException;
use ExpressiveValidator\Exception\MethodNotAllowedException;
use ExpressiveValidator\Response\JsonExceptionResponse;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SebastianBergmann\GlobalState\RuntimeException;
use ExpressiveValidator\Middleware\ErrorMiddleware;
use ExpressiveValidator\Validator\ValidationFailedException;
use ExpressiveValidator\Validator\ValidationResult;

class ErrorMiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $request;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    public function setUp()
    {
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
    }

    public function testInvocationWithMethodNotAllowed()
    {
        $next = function ($requestIn, $responseIn) {
            $this->assertEquals($this->request, $requestIn);
            $this->assertInstanceOf(JsonExceptionResponse::class, $responseIn);
        };
        $errorMiddleware = new ErrorMiddleware();
        $errorMiddleware->__invoke($this->getMock(MethodNotAllowedException::class),
            $this->request,
            $this->response, $next);
        $errorMiddleware->__invoke($this->getMock(EntityNotFoundException::class),
            $this->request,
            $this->response, $next);

        $error = $this->getMock(ValidationFailedException::class);
        $error->expects($this->once())
        ->method('getValidationResult')
        ->willReturn(
            $this->getMockBuilder(ValidationResult::class)
            ->disableOriginalConstructor()
            ->getMock()
        );

        $errorMiddleware->__invoke($error,
            $this->request,
            $this->response, $next);
        $errorMiddleware->__invoke('',
            $this->request,
            $this->response, $next);
    }

    public function testInvocationWillThrowError()
    {
        $this->setExpectedException(RuntimeException::class);
        $next = function ($requestIn, $responseIn) {
            $this->assertEquals($this->request, $requestIn);
            $this->assertInstanceOf(JsonExceptionResponse::class, $responseIn);
        };
        $errorMiddleware = new ErrorMiddleware();

        $errorMiddleware->__invoke($this->getMock(RuntimeException::class),
            $this->request,
            $this->response, $next);

    }
}
