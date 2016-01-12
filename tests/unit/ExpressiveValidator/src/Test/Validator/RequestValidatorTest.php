<?php
namespace ExpressiveValidator\Test\Validator;

use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\RequestValidator;
use ExpressiveValidator\Validator\ValidationResultInterface;
use Zend\Diactoros\PhpInputStream;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Stratigility\Http\Request;

class RequestValidatorTest extends PHPUnit_Framework_TestCase
{

    use ValidatorProviderTrait;

    /**
     * @covers StdLib\Validator\RequestValidator::parseIncomingParams
     * @covers StdLib\Validator\RequestValidator::get
     * @covers StdLib\Validator\RequestValidator::getParsedAttributes
     * @covers StdLib\Validator\RequestValidator::__construct
     * @covers StdLib\Validator\RequestValidator::get
     */
    public function testPost()
    {
        $request = $this->createRequest(static::$urlValid, 'POST', 'application/x-www-form-urlencoded');

        $requestValidator = new RequestValidator($request, $this->createRouter());
        $this->assertArrayHasKey('test', $requestValidator->getParsedBody());
        $this->assertArrayHasKey('query', $requestValidator->getParsedBody());
        $this->assertArrayHasKey('postTest', $requestValidator->getParsedBody());
        $this->assertArrayHasKey('agreement_id', $requestValidator->getParsedBody());
        $this->assertEquals('query', $requestValidator->get('query'));
        $this->assertNull($requestValidator->get('queryNotFound'));
        $this->assertEquals(0, $requestValidator->get('queryNotFound', 0));
    }

    /**
     * @covers StdLib\Validator\RequestValidator::getMethod
     */
    public function testGetMethod ()
    {
        $requestGET = $this->createRequest(static::$urlValid, 'GET');
        $requestValidator = new RequestValidator($requestGET, $this->createRouter());
        $this->assertEquals('GET', $requestValidator->getMethod());

        $requestPOST = $this->createRequest(static::$urlValid, 'POST');
        $requestValidator = new RequestValidator($requestPOST, $this->createRouter());
        $this->assertEquals('POST', $requestPOST->getMethod());
    }

    /**
     * @covers StdLib\Validator\RequestValidator::withAttribute
     * @covers StdLib\Validator\RequestValidator::getAttribute
     * @covers StdLib\Validator\RequestValidator::getUri
     */
    public function testWithAttribute ()
    {
        $request = $this->createRequest(static::$urlValid, 'GET');
        $requestValidator = new RequestValidator($request, $this->createRouter());

        $newRequest =  $requestValidator->withAttribute('test', 'test');

        $this->assertInstanceOf(RequestValidator::class, $newRequest);

        $this->assertEquals($newRequest->getAttribute('noFound'), null);
        $this->assertEquals($newRequest->getAttribute('test'), 'test');
        $this->assertEquals($newRequest->getUri(), $request->getUri());
    }

    /**
     *  @covers StdLib\Validator\RequestValidator::parseIncomingParams
     * @covers StdLib\Validator\RequestValidator::get
     * @covers StdLib\Validator\RequestValidator::getParsedAttributes
     * @covers StdLib\Validator\RequestValidator::getParsedBody
     */
    public function testParsedBody ()
    {
        $request = $this->createRequest(static::$urlValid, 'GET', 'application/json');
        $requestValidator = new RequestValidator($request, $this->createRouter());

        $this->assertArrayHasKey('test', $requestValidator->getParsedBody());
        $this->assertArrayHasKey('query', $requestValidator->getParsedBody());
        $this->assertArrayHasKey('jsonTest', $requestValidator->getParsedBody());
        $this->assertArrayHasKey('agreement_id', $requestValidator->getParsedBody());
    }

    /**
     * @covers StdLib\Validator\RequestValidator::getValidationResult
     * @covers StdLib\Validator\RequestValidator::setValidationResult
     */
    public function testSetGetValidationResult ()
    {
        $request = $this->createRequest(static::$urlValid, 'GET');
        $requestValidator = new RequestValidator($request, $this->createRouter());
        $validationResult = $this->getMock(ValidationResultInterface::class);
        $requestValidator->setValidationResult($validationResult);
        $this->assertEquals($validationResult, $requestValidator->getValidationResult());

    }


    /**
     * @param $uriString
     * @param string $method
     * @param string $contentType
     * @return Request
     */
    private function createRequest($uriString, $method = 'GET', $contentType = 'application/json')
    {
        if ($contentType === 'application/json') {
            $body = json_encode([
                'jsonTest' => 'jsonTest'
            ]);
        } else {
            $body = 'postTest=postTest';
        }
        $serverParams = [
            'QUERY_STRING' => 'query=query',
            'CONTENT_TYPE' => $contentType
        ];
        $uri = $this->getMockBuilder(Uri::class)
        ->setConstructorArgs([$uriString])
        ->getMock();

        $request = $this->getMockBuilder(ServerRequest::class)
        ->setConstructorArgs([
            $serverParams,
            [],
            $uri,
            $method
        ])
        ->getMock();

        // getBody() mock
        $request->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($body));

        // getUri()
        $request->expects($this->any())
        ->method('getUri')
        ->willReturn($uri);

        $request->expects($this->any())
        ->method('getQueryParams')
        ->willReturn([
            'query' => 'query'
        ]);
        // getMethod stub
        $request->expects($this->any())
        ->method('getMethod')
        ->willReturn($method);

        $request->expects($this->any())
        ->method('withParsedBody')
        ->willReturnSelf();
        $request->expects($this->any())
        ->method('getParsedBody')
        ->willReturn([
            'test' => 'testValues'
        ]);
        $request->expects($this->any())
        ->method('getUploadedFiles')
        ->willReturn([]);
        $request->expects($this->any())
        ->method('getServerParams')
        ->willReturn($serverParams);
        return $request;
    }
}