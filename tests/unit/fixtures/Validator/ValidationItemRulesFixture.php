<?php

namespace ExpressiveValidatorFixture\Test\Fixture\Validator;

use Psr\Http\Message\ServerRequestInterface;
use ExpressiveValidator\Validator\ValidateIfPresent;
use ExpressiveValidator\Validator\ValidationRulesInterface;
use ExpressiveValidator\Validator\ValidationRulesConstructorTrait;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;

class ValidationItemRulesFixture implements ValidationRulesInterface
{
    use ValidationRulesConstructorTrait;

    /**
     * Return a class mapping
     * of the validations
     * @return \array[] mixed
     */
    public function getValidationRules()
    {
        return [
            'status_id' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
                Regex::class => [
                    'pattern' => '/^\S{8,8}(-\S{4,4}){3,3}-\S{12,12}$/'
                ],
            ],
            'amount' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'how_much_collected' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'sales_tax' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
        ];
    }

    /**
     * Return the error messages
     * @return array [] mixed
     */
    public function getMessages()
    {
        return [
            'status_id' => [
                NotEmpty::class => 'Please provide the status id for the item',
                Regex::class => 'status_id must have the following pattern xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            ],
            'amount' => [
                NotEmpty::class => 'Must provide item amount field',
            ],
            'how_much_collected' => [
                NotEmpty::class => 'Must provide the how_much_collected field for the item',
            ],

            'sales_tax' => [
                NotEmpty::class => 'Must provide the sales_tax field for item',
            ],
        ];
    }
}
