<?php
namespace ExpressiveValidator\Test\CustomValidators;

use DateInterval;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\CustomValidators\IsCronExpressionIntervalGreater;

class IsCronExpressionIntervalGreaterTest extends PHPUnit_Framework_TestCase
{

    public function testThrowError() {
        $this->setExpectedException(\Exception::class);
        new IsCronExpressionIntervalGreater([]);
    }
    public function testValidation()
    {
        $validatorCronExpression = new IsCronExpressionIntervalGreater([
            'interval' => new DateInterval('P2D')
        ]);
        $this->assertFalse($validatorCronExpression->isValid('*/5 * * * * *'));
        $this->assertContains(
            "Cron expression '*/5 * * * * *' interval must be greater than 2 day(s)",
            array_values($validatorCronExpression->getMessages())[0]
        );

        $validatorCronExpression = new IsCronExpressionIntervalGreater([
            'interval' => new DateInterval('PT5M')
        ]);
        $this->assertTrue($validatorCronExpression->isValid('*/5 * * * * *'));
        $this->assertEmpty($validatorCronExpression->getMessages());

        $validatorCronExpression = new IsCronExpressionIntervalGreater([
            'interval' => new DateInterval('PT7S')
        ]);
        $this->assertTrue($validatorCronExpression->isValid('*/1 * * * * *'));
        $this->assertEmpty($validatorCronExpression->getMessages());
        $validatorCronExpression = new IsCronExpressionIntervalGreater([
            'interval' => new DateInterval('PT7H')
        ]);
        $this->assertFalse($validatorCronExpression->isValid('* */1 * * * *'));
        $this->assertContains(
            "Cron expression '* */1 * * * *' interval must be greater than 7 hour(s)",
            array_values($validatorCronExpression->getMessages())[0]
        );
    }
}
