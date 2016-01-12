<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Carbon\Carbon;
use Zend\Form\Element\DateTime;
use Zend\Validator\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Validator\AbstractValidator;

class ValidDateTime extends AbstractValidator
{
    const INVALID = "notValid";

    protected $dateFormat = \DateTime::ISO8601;

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "'%value%' must be a valid datetime"
    ];
    public function __construct($options = null)
    {
        parent::__construct($options);
        if ($options && isset($options['dateFormat'])) {
            $this->setDateFormat($options['dateFormat']);
        }
    }
    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->setValue($value);
        $d = \DateTime::createFromFormat($this->dateFormat, $value);
        $valid = $d && $d->format($this->dateFormat) == $value;
        if (!$valid) {
            $this->error(self::INVALID);
            return false;
        } else {
            return true;
        }
    }
}
