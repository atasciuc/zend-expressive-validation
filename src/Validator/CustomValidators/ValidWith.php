<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Zend\Validator\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Validator\AbstractValidator;

class ValidWith extends AbstractValidator
{
    const INVALID = "notFound";

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "'%value%' must be used with '%valueToBePresent%'"
    ];

    /**
     * @var
     */
    public $valueToBePresent;

    /**
     * @var array
     */
    protected $messageVariables = [
        'valueToBePresent' => 'valueToBePresent'
    ];

    /***
     * @param array $options ['valueToBePresent', 'request']
     */
    public function __construct(array $options)
    {
        if (!isset($options['valueToBePresent']) ||
            empty($options['valueToBePresent'])) {
            throw new RuntimeException('Key to validate against must be present in the options');
        }
        if (!isset($options['request']) ||
            empty($options['request']) ||
            !$options['request'] instanceof ServerRequestInterface) {
            throw new RuntimeException('Request must be present and be an instance of ServerRequestInterface');
        }
        $this->valueToBePresent = $options['valueToBePresent'];
        parent::__construct($options);
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->setValue($value);
        /**
         * @var $request ServerRequestInterface
         */
        $request = $this->getOptions()['request'];
        $valueFound = $request->getParsedBody()[$this->getOptions()['valueToBePresent']];
        if (!isset($valueFound) || empty($valueFound)) {
            $this->error(self::INVALID);
            return false;
        } else {
            return true;
        }
    }
}
