<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Db\RecordExists;

class IsArray extends AbstractValidator
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
        self::INVALID => "'%value%' must be an array"
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
        if (is_array($value)) {
            return true;
        } else {
            $this->error(self::INVALID, $value);
            return false;
        }
    }
}
