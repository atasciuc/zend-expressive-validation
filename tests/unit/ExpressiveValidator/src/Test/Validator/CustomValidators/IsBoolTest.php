<?php
namespace ExpressiveValidator\Test\CustomValidators;

use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\CustomValidators\IsBool;
use ExpressiveValidator\Validator\RequestValidator;
use Zend\Validator\Exception\RuntimeException;

class IsBoolTest extends PHPUnit_Framework_TestCase
{
    public function testValidation()
    {
        $isBool = new IsBool();
        $this->assertTrue($isBool->isValid(true));
        $this->assertTrue($isBool->isValid(false));
        $this->assertTrue($isBool->isValid("true"));
        $this->assertTrue($isBool->isValid("false"));
        $this->assertTrue($isBool->isValid(1));
        $this->assertTrue($isBool->isValid(0));
        $this->assertEmpty($isBool->getMessages());
        $this->assertTrue($isBool->isValid(''));
        $this->assertTrue($isBool->isValid(null));
        $this->assertFalse($isBool->isValid("stringValue"));

        $this->assertContains(
            'not a boolean',
            array_values($isBool->getMessages())[0]
        );
    }
}
