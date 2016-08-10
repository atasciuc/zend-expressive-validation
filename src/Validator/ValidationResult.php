<?php

namespace ExpressiveValidator\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use Zend\Expressive\Router\RouterInterface;
use Zend\Http\Request;
use Zend\Stdlib\RequestInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\EmailAddress;
use Zend\Validator\StringLength;
use ExpressiveValidator\Validator\RequestValidator;
use ExpressiveValidator\Validator\DbValidatorInterface;

class ValidationResult implements ValidationResultInterface
{

    /**
     * @var ValidationRulesInterface
     */
    private $rules;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $dataToValidate;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $isValid = true;

    /**
     * @var array Holds the error messages
     */
    private $errorMessages = [];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @inheritdoc
     */
    public function __construct(
        array $dataToValidate,
        ValidationRulesInterface $rulesInterface,
        EntityManagerInterface $entityManagerInterface,
        ServerRequestInterface $request
    ) {
        $this->rules = $rulesInterface;
        $this->dataToValidate = $dataToValidate;
        $this->entityManager = $entityManagerInterface;
        $this->request = $request;
        $this->validateRequest();
        if ($request instanceof RequestValidator) {
            $request->setValidationResult($this);
        }
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets the validate data
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the error messages
     * @return mixed
     */
    public function getMessages()
    {
        return $this->errorMessages;
    }

    /**
     * Returns true if valid or false otherwise
     * @return mixed
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * Return true if not valid or false otherwise
     * @return mixed
     */
    public function notValid()
    {
        return !$this->valid();
    }

    /**
     * Validates the request
     */
    private function validateRequest()
    {
        foreach ($this->rules->getValidationRules() as $propertyToValidate => $rule) {
            /**
             * Optional present and not satisfied
             */
            if (is_array($rule) && !$this->checkNeedsValidation($propertyToValidate, $rule)) {
                continue;
            }
            $validOuter = true;
            /**
             * Get the value to be validated
             */
            $data = $this->extractValueFromData($propertyToValidate);
            $hasDeepLink = false;
            /**
             * Each rule in the validation rules array
             */
            foreach ($rule as $ruleValidator => $arg) {
                // DeepLinking validation
                $validationDeepCheck =  $this->validateDeepLinkingObject($ruleValidator, $data, $propertyToValidate);

                if ($validationDeepCheck === -1 || $validationDeepCheck === 1) {
                    continue;
                }
                $validator = $this->decorateValidatorClass($propertyToValidate, $ruleValidator, $arg);
                $isValid = $validator->isValid($this->extractValueFromData($propertyToValidate));
                if ($isValid) {
                    if ($validator instanceof DbValidatorInterface && $validator->isAutoloaded()) {
                        $data = $validator->getCollection();
                    }
                    $this->addValidatedData($propertyToValidate, $data);
                } else {
                    $this->addInvalidMessage($propertyToValidate, $validator->getMessages());
                }
            }
        }
    }

    /***
     * Adds the validated data to the data array
     * @param $property
     * @param $value
     */
    private function addValidatedData($property, $value)
    {
        if (!isset($this->data[$property])) {
            $this->data[$property] = [];
        }
        // Disregard adding new values if there is an object value
        if (is_object($this->data[$property])) {
            return;
        }
        $this->data[$property] = $value;
    }
    /**
     * Validates inner objects will return 1 if
     * valid or 0 if no validation performed
     * or -1 if invalid
     * @param $validationObject
     * @param $data
     * @param $propertyToValidate
     * @return bool
     */
    private function validateDeepLinkingObject($validationObject, $data, $propertyToValidate)
    {
        $validationResult = 0;
        $dataParsed = null;
        /**
         * Check if the validator interface is the validation procedure for
         * the value in the data, if so this means that it is
         * a deep validation and we need to validate this item
         * as a separate instance
         */
        if (in_array(ValidationRulesInterface::class, class_implements($validationObject))) {
            $validationResult = 1;
            if (count($data) !== count($data, COUNT_RECURSIVE)) {
                $dataParsed = [];
                foreach ($data as $key => $deepLinkDataItem) {
                    $deepLinkValidator = $this->createValidationDeepLinkResultInstance(
                        $deepLinkDataItem,
                        $validationObject
                    );
                    if ($deepLinkValidator->notValid()) {
                        $validationResult = -1;
                        $this->addInvalidMessage(
                            $key . '. ' . $propertyToValidate,
                            $deepLinkValidator->getMessages()
                        );
                    } else {
                        $dataParsed[] = $deepLinkValidator->getData();
                    }
                }
            } else {
                $validationResult = 1;
                $deepLinkValidator = $this->createValidationDeepLinkResultInstance(
                    $data,
                    $validationObject
                );
                if ($deepLinkValidator->notValid()) {
                    $validationResult = -1;
                    $this->addInvalidMessage(
                        $propertyToValidate,
                        $deepLinkValidator->getMessages()
                    );
                } else {
                    $dataParsed = $deepLinkValidator->getData();
                }
            }
        }

        if ($validationResult == 1) {
            $this->addValidatedData($propertyToValidate, $dataParsed);
        }

        return $validationResult;
    }

    /**
     * Attaches the invalid messages to the messages
     * this will set the result to be invalid
     * @param $propertyItem
     * @param array $messages validation messages
     * @return $this|mixed
     */
    public function addInvalidMessage($propertyItem, array $messages)
    {
        $this->isValid = false;
        if (!isset($this->errorMessages[$propertyItem])) {
            $this->errorMessages[$propertyItem] = [];
        }
        $this->errorMessages[$propertyItem][] = array_merge(
            $this->errorMessages[$propertyItem],
            array_values($messages)
        );

        return $this;
    }
    /**
     * @param $keyToValidate
     * @param [] AbstractValidator $rule
     * @return bool
     */
    private function checkNeedsValidation($keyToValidate, array $rules)
    {
        $hasValidateIfPresent = array_key_exists(ValidateIfPresent::class, $rules);

        if ($hasValidateIfPresent && $this->extractValueFromData($keyToValidate) !== null) {
            return true;
        } elseif (!$hasValidateIfPresent) {
            return true;
        }

        return false;
    }
    /**
     * @param $key
     * @return string
     */
    private function extractValueFromData($key)
    {
        return isset($this->dataToValidate[$key]) ? $this->dataToValidate[$key] : null;
    }

    /**
     * Sets the error messages for the validator
     * @param $propertyToValidate
     * @param $validatorClass
     * @return array
     * @internal param $key
     * @internal param array $messages
     */
    private function findErrorMessagesForValidator($propertyToValidate, $validatorClass)
    {
        $allMessages = $this->rules->getMessages();
        if (isset($allMessages[$propertyToValidate]) && isset($allMessages[$propertyToValidate][$validatorClass])) {
            return $allMessages[$propertyToValidate][$validatorClass];
        } else {
            return [];
        }
    }

    /**
     * Validates each value
     * @param string $validator
     * @param array| callable $arg
     * @return AbstractValidator
     */
    private function createValidatorInstance($validator, $arg = [])
    {
        if (in_array(DbValidatorInterface::class, class_implements($validator))) {
            $validatorInstance = new $validator($this->entityManager, $arg);
        } elseif (is_callable($arg)) {
            $validatorInstance = $arg();
        } else {
            /**
             * @var AbstractValidator $validatorInstance
             */
            $validatorInstance = new $validator($arg);
        }

        return $validatorInstance;
    }

    /**
     * Creates an instance of the validation
     * object which will validate the value
     * @param $propertyToValidate
     * @param $ruleValidator
     * @param $arg
     * @return AbstractValidator
     */
    private function decorateValidatorClass($propertyToValidate, $ruleValidator, $arg)
    {

        $validator = $this->createValidatorInstance($ruleValidator, $arg);
        // Set the messages
        if (($validator instanceof AbstractValidator) || method_exists($validator, 'setMessages')) {
            $messages = $this->findErrorMessagesForValidator($propertyToValidate, $ruleValidator);
            if (is_string($messages)) {
                $validator->setMessage($messages);
            } else {
                $validator->setMessages($messages);
            }
        }
        return $validator;
    }

    /**
     * @param $data
     * @param ValidationRulesInterface $rulesProvider
     * @return ValidationResult
     */
    private function createValidationDeepLinkResultInstance($data, $rulesProvider)
    {
        return $deepLinkValidator = new self(
            !is_array($data) ? [$data] : $data,
            new $rulesProvider($this->request),
            $this->entityManager,
            $this->request
        );
    }
}
