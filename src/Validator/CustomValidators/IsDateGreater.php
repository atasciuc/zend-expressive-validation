<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Carbon\Carbon;
use Cron\CronExpression;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use Zend\Validator\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Validator\AbstractValidator;

class IsDateGreater extends AbstractValidator
{
    const INVALID = "notValid";

    protected $dateFormat = \DateTime::ISO8601;

    /**
     * @var DateInterval
     */
    protected $interval;

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "Date '%value%' must be greater than %minDate%"
    ];

    /**
     * @var
     */
    public $minDate;

    /**
     * @var array
     */
    protected $messageVariables = [
        'minDate' => 'minDate'
    ];
    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = null)
    {
        if (!isset($options['minDate'])) {
            $this->minDate = Carbon::now();
        } elseif (isset($options['minDate']) && !$options['minDate'] instanceof DateTime) {
            throw new \InvalidArgumentException('Invalid datetime object');
        } else {
            $this->minDate = Carbon::instance($options['minDate']);
        }

        if ($options && isset($options['dateFormat'])) {
            $this->setDateFormat($options['dateFormat']);
        }

        parent::__construct($options);
    }
    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->setValue($value);
        $datePassed = Carbon::createFromFormat($this->getDateFormat(), $value);
        if ($datePassed->lte($this->minDate)) {
            $this->error(self::INVALID);
            return false;
        }
        return true;
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
}
