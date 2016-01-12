<?php
namespace ExpressiveValidator\Test\CustomValidators;

use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\CustomValidators\ValidWith;
use ExpressiveValidator\Validator\RequestValidator;
use Zend\Validator\Exception\RuntimeException;

class ValidWithTest extends PHPUnit_Framework_TestCase
{
    public function testExceptionThrowingNoValue() {
        $this->setExpectedException(RuntimeException::class);
        $validWith = new ValidWith([
            "request" => $this->getMockBuilder(RequestValidator::class)
            ->disableOriginalConstructor()->getMock()
        ]);
    }

    public function testExceptionThrowingNoRequest() {
        $this->setExpectedException(RuntimeException::class);
        $validWith = new ValidWith([
            "valueToBePresent" => 'tt',
        ]);
    }

    public function testValidationValuePresent()
    {
        $request = $this->getMockBuilder(RequestValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
            'size' => 2
        ]);
        $validWith = new ValidWith([
            "valueToBePresent" => 'size',
            "request"   => $request
        ]);
        $this->assertTrue($validWith->isValid('*'));
        $this->assertEmpty($validWith->getMessages());
    }
    public function testValidationValueNotPresent()
    {
        $request = $this->getMockBuilder(RequestValidator::class)
        ->disableOriginalConstructor()
        ->getMock();
        $request->expects($this->once())
        ->method('getParsedBody')
        ->willReturn([
            'size' => ''
        ]);
        $validWith = new ValidWith([
            "valueToBePresent" => 'size',
            "request"   => $request
        ]);
        $this->assertFalse($validWith->isValid('*'));
        $this->assertContains(
            'must be used with \'size\'',
            array_values($validWith->getMessages())[0]
        );
    }
}
