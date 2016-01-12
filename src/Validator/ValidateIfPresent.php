<?php
namespace ExpressiveValidator\Validator;

use Doctrine\ORM\EntityManagerInterface;
use StdLib\Validator\DbValidatorInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Db\RecordExists;
use Zend\Validator\NotEmpty;

/**
 * Helper to validate optional parameters
 * Class ValidateIfPresent
 * @package SchedulerApi\Validators\CustomValidators
 */
class ValidateIfPresent extends NotEmpty
{

}
