Expressive Validator
====
Validation library for the zend-expressive
(http://zend-expressive.readthedocs.org/en/latest)

## Installation
Using composer!
```bash
composer require atasciuc/zend-expressive-validation
```

## Usage
Create a validator factory
```php
/**
 * Instantiates the validator
 * Class YourValidatorFactoryClass
 */
class YourValidatorFactoryClass
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('orm.default'); // = null
        return new Validator(
            $container->get(OptionsExtractor::class),
            $container->get(RouterInterface::class),
            $entityManager
        );
    }
}

```
## Note:
The entityManager is optional and required only if you want to use database validation classes such as `EntityExist`

```
/**
 * Instantiates the validator
 * Class ValidatorFactory
 * @package SchedulerApi\Validators
 */
class ValidatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('orm.default');
        return new Validator(
            $container->get(OptionsExtractor::class),
            $container->get(RouterInterface::class),
            $entityManager
        );
    }
}
```

Add the validator classes to the `dependecies.php` of your project
```php
<?php

use ExpressiveValidator\Middleware\ErrorMiddleware;
use ExpressiveValidator\Validator\OptionExtractorFactory;
use ExpressiveValidator\Validator\OptionsExtractor;
use ExpressiveValidator\Validator\ValidationMiddleware;
use ExpressiveValidator\Validator\ValidationMiddlewareFactory;
use ExpressiveValidator\Validator\Validator;

return [
    'dependencies' => [
        'abstract_factories' => [
        ],
        'invokables' => [
            ErrorMiddleware::class => ErrorMiddleware::class,
        ],
        'factories' => [
            Zend\Expressive\Application::class => Zend\Expressive\Container\ApplicationFactory::class,
            OptionsExtractor::class => OptionExtractorFactory::class,
            Validator::class => YourValidatorFactoryClass::class,
            ValidationMiddleware::class => ValidationMiddlewareFactory::class,
        ],
        'shared' => [
        ]
    ]
];

```
Edit your `middleware-pipeline.global` and register the validation middlewares
```php
<?php   
use ExpressiveValidator\Middleware\ErrorMiddleware;
use ExpressiveValidator\Validator\ValidationMiddleware;

return [
    // This can be used to seed pre- and/or post-routing middleware
    'middleware_pipeline' => [
        // An array of middleware to register prior to registration of the
        // routing middleware
        'pre_routing' => [
            [
                'middleware' => ValidationMiddleware::class,
            ],
            //[
            // Required:
            //    'middleware' => 'Name of middleware service, or a callable',
            // Optional:
            //    'path'  => '/path/to/match',
            //    'error' => true,
            //],
        ],

        // An array of middleware to register after registration of the
        // routing middleware
        'post_routing' => [
            [
                'middleware' => ErrorMiddleware::class,
                'error'      => true
            ],
            //[
            // Required:
            //    'middleware' => 'Name of middleware service, or a callable',
            // Optional:
            //    'path'  => '/path/to/match',
            //    'error' => true,
            //],
        ],
    ],
];
```
### Note
Or you can add your own `ErrorMiddleware` and register it in you dependencies, example of the one provided:
```php
class ErrorMiddleware
{
    /**
     * @param mixed $error
     * @param Request $request
     * @param Response $response
     * @param callable|null $out
     * @return
     * @throws Exception
     */
    public function __invoke($error, Request $request, Response $response, callable $out = null)
    {
        if (!($error instanceof Exception)) {
            $error = new MethodNotAllowedException();
        }
        switch (true) {
            case $error instanceof MethodNotAllowedException || $error instanceof EntityNotFoundException:
                return $out($request, new JsonExceptionResponse($error->getCode(), $error->getMessage()));
            case ($error instanceof ValidationFailedException):
                $messages = $error->getValidationResult()->getMessages();
                return $out($request, new JsonExceptionResponse(
                    $error->getCode(),
                    count($messages) > 0 ? $messages[0] : 'Something is not right'
                ));
            default:
                throw $error;
        }
    }
}
```

To your route add the validation to the route:
```php
'routes' => [
        [
            'name' => 'user',
            'path' => '/user/{id}',
            'allowed_methods' => ['GET'],
            'middleware' => UserAction::class,
            'options' => [
                'validation' =>  [
                    'GET' => GetUserValidationRules::class
                ]
            ]
        ]
    ],
```

The validation might be aplyed to the http method or to all the methods:
```php
'options' => [
        'validation' =>  [
            '*' => GetUserValidationRules::class
        ]
    ]
```
The validation rules class my comply to the - [ValidationRulesInterface](https://github.com/atasciuc/zend-expressive-validation/blob/master/src/Validator/ValidationRulesInterface.php)

Example of the validation rules: 
```php
class GetUserValidationRules implements ValidationRulesInterface
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
            'id' => [
                NotEmpty::class => [
                    'locale' => 'en'
                ],
                Regex::class => [
                    'pattern' => $this->idRegex
                ],
                EntityExist::class => [
                    'entity' => User::class,
                    'field' => 'id'
                ]
            ]
        ];
    }

    /**
     * Return the error messages
     * @return array [] mixed
     */
    public function getMessages()
    {
        return [
            'id' => [
                NotEmpty::class => 'Please provide the user',
                Regex::class => 'user id must have the following pattern xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                EntityExist::class => 'This user does not exist'
            ]
        ];
    }
}
```

Now in your action you can extract the validated data:
```php
 public function __invoke(Request $request, Response $response, callable $next = null)
    {
        $data = $request->getValidationResult()->getData();

        return $next($request, new JsonResponse($data));
    }
```