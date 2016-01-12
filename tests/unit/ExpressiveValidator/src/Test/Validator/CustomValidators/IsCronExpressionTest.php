<?php
namespace ExpressiveValidator\Test\CustomValidators;

use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\CustomValidators\IsCronExpression;

class IsCronExpressionTest extends PHPUnit_Framework_TestCase
{

    public function testValidation()
    {
        $validatorCronExpression = new IsCronExpression();
        $this->assertFalse($validatorCronExpression->isValid('*'));
        $this->assertContains(
            'must be a valid cron expression',
            array_values($validatorCronExpression->getMessages())[0]
        );
        $validatorCronExpression = new IsCronExpression();
        $this->assertTrue($validatorCronExpression->isValid('*/5 * * * * *'));
        $this->assertEmpty($validatorCronExpression->getMessages());
    }
}
