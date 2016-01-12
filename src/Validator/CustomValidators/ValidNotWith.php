<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Zend\Validator\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Validator\AbstractValidator;

class ValidNotWith extends AbstractValidator
{
    const INVALID = "notFound";

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::INVALID => "'%value%' must not be used with '%valueNotToBePresent%'"
    ];

    /**
     * @var
     */
    public $valueNotToBePresent;

    /**
     * @var array
     */
    protected $messageVariables = [
        'valueNotToBePresent' => 'valueNotToBePresent'
    ];

    /***
     * @param array $options ['valueNotToBePresent', 'request']
     */
    public function __construct(array $options)
    {
        if (!isset($options['valueNotToBePresent']) ||
            empty($options['valueNotToBePresent'])) {
            throw new RuntimeException('Key to validate against must be present in the options');
        }
        if (!isset($options['request']) ||
            empty($options['request']) ||
            !$options['request'] instanceof ServerRequestInterface) {
            throw new RuntimeException('Request must be present and be an instance of ServerRequestInterface');
        }
        $this->valueNotToBePresent = $options['valueNotToBePresent'];
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
        $options = $this->getOptions();
        $request = $options['request'];
        $found = array_key_exists($options['valueNotToBePresent'], $request->getParsedBody());
        if ($found) {
            $this->error(self::INVALID);
            return false;
        } else {
            return true;
        }
    }
}
