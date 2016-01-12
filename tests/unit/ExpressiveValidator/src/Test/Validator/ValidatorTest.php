<?php
namespace ExpressiveValidator\Test\Validator;

use ExpressiveValidator\Validator\OptionsExtractor;
use ExpressiveValidator\Validator\ValidationClassNotExists;
use ExpressiveValidator\Validator\ValidationResultInterface;
use ExpressiveValidator\Validator\Validator;
use ExpressiveValidatorFixture\Test\Fixture\Validator\ValidationRulesFixture;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Validator\NotEmpty;

class ValidatorTest extends PHPUnit_Framework_TestCase
{

    use ValidatorProviderTrait;

    public function testValidateNoValidationApplied()
    {
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $validator = new Validator($optionExtractor, $this->router, $this->getEntityManagerMock());
        $this->assertTrue($validator->validate($this->createRequest(static::$urlValid)));
        $this->assertTrue($validator->validate($this->createRequest(static::$urlInvalid)));
    }
    public function testValidateValidationAppliedNoErrors()
    {
        /**
         * Apply the validation to the config
         */
        $this->applyValidationConfig();
        $this->config[0]['options'] =  [
            'validation' =>  [
                'GET' => ValidationRulesFixture::class
            ]
        ];
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $validator = new Validator($optionExtractor, $this->router, $this->getEntityManagerMock());

        /**
         * @var ValidationResultInterface $validationResult
         */
        $validationResult = $validator->validate($this->createRequest(static::$urlValid));
        /**
         * Should be valid
         */
        $this->assertInstanceOf(ValidationResultInterface::class, $validationResult);
        $this->assertTrue($validationResult->valid());
        $this->assertFalse($validationResult->notValid());
        $this->assertEmpty($validationResult->getMessages());
        $this->assertArrayHasKey('agreement_id', $validationResult->getData());
        $this->assertEquals( $validationResult->getData()['agreement_id'], '3435-234234');
    }

    public function testValidateNotSpecified()
    {
        /**
         * Apply the validation to the config
         */
        $this->applyValidationConfig();
        $this->config[0]['options'] =  [
            'validation' => []
        ];
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $validator = new Validator($optionExtractor, $this->router, $this->getEntityManagerMock());

        /**
         * @var ValidationResultInterface $validationResult
         */
        $validationResult = $validator->validate($this->createRequest(static::$urlValid));
        $this->assertTrue($validationResult);

    }

    public function testThrowsException()
    {
        $this->setExpectedException(ValidationClassNotExists::class);
        /**
         * Apply the validation to the config
         */
        $this->applyValidationConfig();

        $this->config[0]['options'] =  [
            'validation' =>  [
                '*' => 'FullClass'
            ]
        ];

        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $validator = new Validator($optionExtractor, $this->router, $this->getEntityManagerMock());

        /**
         * @var ValidationResultInterface $validationResult
         */
        $validationResult = $validator->validate($this->createRequest(static::$urlValid));

    }


    public function testValidateValidationAppliedWithErrors()
    {
        /**
         * Apply the validation to the config
         */
        $this->applyValidationConfig();
        $router = $this->getMock(FastRouteRouter::class);
        $router->expects($this->any())
        ->method('match')
        ->willReturn(RouteResult::fromRouteMatch($this->config[0]['path'],
            function(){},
            []));
        $optionExtractor = new OptionsExtractor($this->config, $router);
        $validator = new Validator($optionExtractor, $router, $this->getEntityManagerMock());

        /**
         * @var ValidationResultInterface $validationResult
         */
        $validationResult = $validator->validate($this->createRequest(static::$urlInvalid));
        /**
         * Should not be valid
         */
        $this->assertInstanceOf(ValidationResultInterface::class, $validationResult);
        $this->assertFalse($validationResult->valid());
        $this->assertTrue($validationResult->notValid());
        $this->assertNotEmpty($validationResult->getMessages());
        $this->assertEmpty($validationResult->getData());
        $this->assertGreaterThan(0, count($validationResult->getMessages()));
        $this->assertContains('Please provide an id for the agreement', $validationResult->getMessages());
    }
}
