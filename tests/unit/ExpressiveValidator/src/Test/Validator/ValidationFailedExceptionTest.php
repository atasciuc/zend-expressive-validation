<?php
namespace ExpressiveValidator\Test\Validator;

use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\OptionsExtractor;
use ExpressiveValidator\Validator\ValidationFailedException;
use ExpressiveValidator\Validator\ValidationResultInterface;
use ExpressiveValidator\Validator\ValidationRulesInterface;
use ExpressiveValidator\Validator\Validator;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouterInterface;
use Doctrine\ORM\EntityManagerInterface;

class ValidationFailedExceptionTest extends PHPUnit_Framework_TestCase
{

    use ValidatorProviderTrait;

    /**
     * @covers StdLib\Validator\Validator::validate
     */
    public function testValidateValidationAppliedWithErrors()
    {
        /**
         * Apply the validation to the config
         */
        $this->applyValidationConfig();
        $optionExtractor = $this->getMockBuilder(OptionsExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $validator = new Validator($optionExtractor, $this->router, $entityManagerMock);

        /**
         * @var ValidationResultInterface $validationResult
         */
        $validationResult = $validator->validate($this->createRequest(static::$urlInvalid));

        $exceptionValidator = new ValidationFailedException();
        $exceptionValidator->setValidationResult($validationResult);
        $this->assertEquals($exceptionValidator->getValidationResult(), $validationResult);

    }


}