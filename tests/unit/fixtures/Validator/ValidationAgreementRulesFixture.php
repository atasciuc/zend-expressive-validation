<?php
namespace ExpressiveValidatorFixture\Test\Fixture\Validator;
use ExpressiveValidator\Validator\CustomValidators\IsCronExpression;
use ExpressiveValidator\Validator\CustomValidators\ValidDateTime;
use ExpressiveValidator\Validator\ValidateIfPresent;
use ExpressiveValidator\Validator\ValidationRulesConstructorTrait;
use ExpressiveValidator\Validator\ValidationRulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;

class ValidationAgreementRulesFixture implements  ValidationRulesInterface
{
    use ValidationRulesConstructorTrait;
    private static $dateRegex = '/^2[0-9]{3,3}-[0,1][0-9]-[0-3][0-9]T\d{2,2}:\d{2,2}:\d{2,2}\+\d{2,2}\d{2,2}$/';
    /**
     * @inheritdoc
     */
    public function getValidationRules()
    {
        return [
            'billing_start_date'  => [
                Regex::class => [
                    'pattern' => self::$dateRegex
                ],
                ValidDateTime::class => []
            ],
            'schedule_expression' => [
                ValidateIfPresent::class => [],
                IsCronExpression::class => [],
            ],
            'schedule_expression_count' => [
                ValidateIfPresent::class => [],
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'customer_id' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
                Regex::class => [
                    'pattern' => '/^\S{8,8}(-\S{4,4}){3,3}-\S{12,12}$/'
                ],
            ],
            'payment_method_id' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
                Regex::class => [
                    'pattern' => '/^\S{8,8}(-\S{4,4}){3,3}-\S{12,12}$/'
                ]
            ],
            'financial_entity_id' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
                Regex::class => [
                    'pattern' => '/^\S{8,8}(-\S{4,4}){3,3}-\S{12,12}$/'
                ],
            ],
            'promotion_id' => [
                ValidateIfPresent::class => [],
                Regex::class => function () { // for callable coverage
                    return new Regex([
                        'pattern' => '/^\S{8,8}(-\S{4,4}){3,3}-\S{12,12}$/'
                    ]);
                },
            ],
            'pre_multiple_amount' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'how_much_collected' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'post_multiple_amount' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'sales_tax' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
            ],
            'items' => [
                ValidateIfPresent::class => [],
                ValidationItemRulesFixture::class => []
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return [
            'billing_start_date'  => [
                Regex::class => 'billing_start_date must have a format of 2005-08-15T15:52:01+0000',
                ValidDateTime::class => 'billing_start_date must be a valid date'
            ],
            'schedule_expression' => [
                IsCronExpression::class => 'schedule_expression must have a valid cron pattern. Ex: */5 * * * * *'
            ],
            'schedule_count' => [
                NotEmpty::class => 'schedule_count must not be empty',
            ],
            'customer_id' => [
                NotEmpty::class => 'Please provide an id for the customer',
                Regex::class => 'customer_id must have a valid pattern xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            ],
            'payment_method_id' => [
                NotEmpty::class => 'Please provide the payment method id',
                Regex::class => 'payment_method_id must have a valid pattern xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            ],
            'financial_entity_id' => [
                NotEmpty::class => 'Please provide the financial entity id',
                Regex::class => 'financial_entity_id must have a valid pattern xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            ],
            'promotion_id' => [
                Regex::class => 'promotion_id must have a valid pattern xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            ],
            'pre_multiple_amount' => [
                NotEmpty::class => 'Must provide the pre_multiple_amount field',
            ],
            'how_much_collected' => [
                NotEmpty::class => 'Must provide the how_much_collected field',
            ],
            'post_multiple_amount' => [
                NotEmpty::class => 'Must provide the post_multiple_amount field',
            ],
            'sales_tax' => [
                NotEmpty::class => 'Must provide the sales_tax field',
            ],
            'items' => [
                NotEmpty::class => 'Must provide agreement items',
            ]
        ];
    }

    public function fakeData()
    {
        return [
            "items"  =>  [
                "status_id"  =>  "00579dc1-9532-11e5-8897-0800279114cs",
                "amount"  =>  0.1,
                "how_much_collected"  =>  0,
                "sales_tax"  =>  "0"
            ],
            "customer_id"  => "2af1f872-7ce7-11e5-9c38-80fa5b131b9s",
            "payment_method_id"  =>  "2af1f872-7ce7-11e5-9c38-80fa5b131b9a",
            "financial_entity_id"  =>  "2af1f872-7ce7-11e5-9c38-80fa5b131b9s",
            "promotion_id"  =>  "00579dc1-9532-11e5-8897-0800279114ca",
            "pre_multiple_amount"  =>  22,
            "post_multiple_amount"  =>  0.2,
            "sales_tax"  =>  0,
            "schedule_expression"  =>  "*/5 * * * * *",
            "schedule_expression_count"  =>  "1",
            "how_much_collected"  =>  0,
            "billing_start_date"  =>  "2005-08-15T15:52:01+0000"
        ];
    }
}