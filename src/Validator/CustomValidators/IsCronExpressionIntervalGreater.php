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

class IsCronExpressionIntervalGreater extends AbstractValidator
{
    const INVALID = "notValid";

    /**
     * @var DateInterval
     */
    protected $interval;

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "Cron expression '%value%' interval must be greater than %intervalString%"
    ];

    /**
     * @var
     */
    public $intervalString;

    /**
     * @var array
     */
    protected $messageVariables = [
        'intervalString' => 'intervalString'
    ];
    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['interval'])) {
            throw new \Exception('Must provide the interval');
        }
        $this->interval = $options['interval'];
        $this->intervalToString();
        parent::__construct($options);
    }
    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->setValue($value);
        $cronExpression = CronExpression::factory($value);
        $firstRun = Carbon::instance($cronExpression->getNextRunDate());
        $secondRun = Carbon::instance($cronExpression->getNextRunDate($firstRun));
        $dateWithAddedInterval = Carbon::instance($firstRun->add($this->interval));
        if (!$secondRun->gte($dateWithAddedInterval)) {
            $this->error(self::INVALID);
            return false;
        }
        return true;
    }

    /**
     * Converts the interval to string
     */
    private function intervalToString()
    {
        switch (true) {
            case ($this->interval->d > 0):
                $this->intervalString = $this->interval->format('%d day(s)');
                break;
            case $this->interval->h > 0:
                $this->intervalString = $this->interval->format('%h hour(s)');
                break;
            case $this->interval->i > 0:
                $this->intervalString = $this->interval->format('%i minute(s)');
                break;
            case $this->interval->s > 0:
                $this->intervalString = $this->interval->format('%s second(s)');
                break;
        }
    }
}
