<?php
namespace ExpressiveValidatorFixture\Test\Fixture\Validator;
use ExpressiveValidator\Validator\ValidationRulesConstructorTrait;
use ExpressiveValidator\Validator\ValidationRulesInterface;
use Zend\Validator\NotEmpty;

class ValidationRulesFixture implements  ValidationRulesInterface
{

    use ValidationRulesConstructorTrait;

    /**
     * @inheritdoc
     */
    public function getValidationRules()
    {
        return [
            'agreement_id' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return [
            'agreement_id' => [
                NotEmpty::class => 'Please provide an id for the agreement'
            ]
        ];
    }
}