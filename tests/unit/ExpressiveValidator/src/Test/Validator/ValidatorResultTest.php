<?php
namespace ExpressiveValidator\Test\Validator;

use ExpressiveValidator\Validator\RequestValidator;
use ExpressiveValidator\Validator\ValidationResult;
use ExpressiveValidatorFixture\Test\Fixture\Validator\ValidationAgreementRulesFixture;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Stratigility\Http\Request;
use Zend\Validator\NotEmpty;

class ValidatorResultTest extends PHPUnit_Framework_TestCase
{

    use ValidatorProviderTrait;

    public function testAddingInvalidMessages()
    {
        $requestValidator = $this->createRequest(static::$urlValid);
        $em = $this->getEntityManagerMock();
        $validationRules = new ValidationAgreementRulesFixture($requestValidator);

        $validationResult = new ValidationResult(
            $validationRules->fakeData(),
            $validationRules,
            $em,
            $requestValidator
        );
        $this->assertTrue($validationResult->valid());
        $this->assertEmpty($validationResult->getMessages());
        $validationResult->addInvalidMessage('customer_id', ['testMessage']);
        $this->assertTrue($validationResult->notValid());
        $this->assertTrue(in_array('testMessage', $validationResult->getMessages()));
    }


    public function testValidationResultReturnsRequest()
    {
        $requestValidator = $this->createRequest(static::$urlValid);
        $em = $this->getEntityManagerMock();
        $validationRules = new ValidationAgreementRulesFixture($requestValidator);

        $validationResult = new ValidationResult(
            $validationRules->fakeData(),
            $validationRules,
            $em,
            $requestValidator
        );
        $this->assertEquals($validationResult->getRequest(), $requestValidator);
        $this->assertEquals($validationRules->fakeData(), $validationResult->getData());
        $this->assertTrue($validationResult->valid());
        $this->assertFalse($validationResult->notValid());
    }

    public function testErrorDetectionsOneLevel()
    {
        $requestValidator = $this->createRequest(static::$urlValid);
        $em = $this->getEntityManagerMock();
        $validationRules = new ValidationAgreementRulesFixture($requestValidator);
        $data = $validationRules->fakeData();

        unset($data['customer_id']);
        unset($data['promotion_id']);
        $validationResult = new ValidationResult(
            $data,
            $validationRules,
            $em,
            $requestValidator
        );
        $this->assertFalse($validationResult->valid());
        $this->assertTrue($validationResult->notValid());
    }

    public function testErrorDetectionsMultiLevels()
    {
        $requestValidator = $this->createRequest(static::$urlValid);
        $em = $this->getEntityManagerMock();
        $validationRules = new ValidationAgreementRulesFixture($requestValidator);
        $data = $validationRules->fakeData();

        unset($data['items']['status_id']);
        $validationResult = new ValidationResult(
            $data,
            $validationRules,
            $em,
            $requestValidator
        );
        $this->assertFalse($validationResult->valid());
        $this->assertTrue($validationResult->notValid());

        $data['items'] = [
          $data['items'],
          $data['items'],
        ];
        // Test multi items
        $validationResult = new ValidationResult(
            $data,
            $validationRules,
            $em,
            $requestValidator
        );
        $this->assertFalse($validationResult->valid());
        $this->assertTrue($validationResult->notValid());
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

        $request = $this->getMockBuilder(RequestValidator::class)
        ->disableOriginalConstructor()
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
        ->willReturnCallback(function () {
            return   [
                'test' => 'testValues'
            ];
        });
        $request->expects($this->any())
        ->method('getUploadedFiles')
        ->willReturn([]);
        $request->expects($this->any())
        ->method('getServerParams')
        ->willReturn($serverParams);
        return $request;
    }
}
