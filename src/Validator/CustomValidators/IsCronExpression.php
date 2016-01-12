<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Cron\CronExpression;
use InvalidArgumentException;
use Zend\Validator\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Validator\AbstractValidator;

class IsCronExpression extends AbstractValidator
{
    const INVALID = "notValid";

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "'%value%' must be a valid cron expression"
    ];

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->setValue($value);
        try {
            CronExpression::factory($value);
            return true;
        } catch (InvalidArgumentException $e) {
            $this->error(self::INVALID);
            return false;
        }
    }
}
