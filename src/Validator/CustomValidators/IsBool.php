<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Doctrine\ORM\EntityManagerInterface;
use StdLib\Validator\DbValidatorInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Db\RecordExists;

class IsBool extends AbstractValidator
{

    const INVALID = "notFound";
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "'%value%' not a boolean"
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws \Zend\Validator\Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
            return true;
        } else {
            $this->error(self::INVALID, $value);
            return false;
        }
    }
}
