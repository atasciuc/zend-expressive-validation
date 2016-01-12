<?php
namespace ExpressiveValidator\Test\CustomValidators;

use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\CustomValidators\ValidDateTime;

class ValidDateTimeValidationTest extends PHPUnit_Framework_TestCase
{

    public function testValidation()
    {
        $validDateTime = new ValidDateTime();
        $this->assertFalse($validDateTime->isValid('2013'));
        $this->assertContains('must be a valid', array_values($validDateTime->getMessages())[0]);
        $validDateTime = new ValidDateTime();
        $this->assertTrue($validDateTime->isValid('2005-08-15T15:52:01+0000'));
    }

    public function testValidationWithCustomFormat()
    {
        $validDateTime = new ValidDateTime(['dateFormat' => \DateTime::ATOM]);
        $this->assertTrue($validDateTime->isValid('2015-12-14T10:34:34-05:00'));
    }
}
