<?php

namespace ExpressiveValidator\Test\Validator;

use Doctrine\ORM\EntityManagerInterface;
use ExpressiveValidator\Validator\ValidationRulesInterface;
use ExpressiveValidatorFixture\Test\Fixture\Validator\ValidationRulesFixture;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

trait ValidatorProviderTrait
{
    private static $urlInvalid = 'http://www.lto.flexshopper.dev/agreements';
    private static $urlValid = 'http://www.lto.flexshopper.dev/agreements/3435-234234';

    /**
     * @var ValidationRulesInterface $validationProvider
     */
    private $validationProvider;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var array
     */
    private $config;

    protected function setUp()
    {
        $this->config = [
            [
                'name' => 'agreements',
                'path' => '/agreements[/{agreement_id}]',
                'middleware' => function(){},
                'allowed_methods' => ['GET', 'DELETE', 'PATCH', 'PUT', 'POST'],
                'options' => []
            ]
        ];
        $router = $this->getMock(FastRouteRouter::class);
        $router->expects($this->any())
        ->method('match')
        ->willReturn(RouteResult::fromRouteMatch($this->config[0]['path'],
            function(){},
            [
                'agreement_id' => '3435-234234'
            ]));
        $this->router = $router;
    }
    /**
     * Helper for applying the validation
     * @param string $method
     */
    private function applyValidationConfig($method = '*')
    {
        $this->config[0]['options'] =  [
            'validation' =>  [
                $method => ValidationRulesFixture::class
            ]
        ];
    }
    /**
     * @param $uriString
     * @param string $method
     * @return ServerRequest
     */
    private function createRequest($uriString, $method = 'GET')
    {

        $uri = new Uri($uriString);
        $request =  new ServerRequest([
            'QUERY_STRING' => 'query=query'
        ], [], $uri, $method);
        return $request->withParsedBody([]);
    }
    /**
     * Creates a router
     * @return FastRouteRouter
     */
    private function createRouter()
    {
        $this->config = [
            [
                'name' => 'agreements',
                'path' => '/agreements[/{agreement_id}]',
                'middleware' => function () {},
                'allowed_methods' => ['GET', 'DELETE', 'PATCH', 'PUT', 'POST'],
                'options' => []
            ]
        ];

        $router = $this->getMock(FastRouteRouter::class);
        $router->expects($this->any())
        ->method('match')
        ->willReturn(RouteResult::fromRouteMatch($this->config[0]['path'],
            function(){},
            [
                'agreement_id' => '3435-234234'
            ]));
        return $router;
    }

    /**
     * Mock the entity manager
     * @return EntityManagerInterface
     */
    private function getEntityManagerMock()
    {
        return $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
    }
}