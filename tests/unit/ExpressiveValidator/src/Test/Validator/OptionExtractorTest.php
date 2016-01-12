<?php
namespace ExpressiveValidator\Test\Validator;

use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ExpressiveValidator\Validator\OptionsExtractor;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use \Zend\Stratigility\Http\Request;

class OptionExtractorTest extends PHPUnit_Framework_TestCase
{
    private $config;
    /**
     * @var RouterInterface $router
     */
    private $router;
    private static $url = 'http://www.lto.flexshopper.dev/agreements/1234';
    protected function setUp()
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
                'agreement_id' => '1234'
            ]));
        $this->router = $router;
    }

    /**
     * @covers StdLib\Validator\OptionsExtractor::getOptionsForRequest
     */
    public function testGetOptionsForRequest()
    {
        /**
         * Test no options with route match
         */
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $optionExtractorEmptyConfig = new OptionsExtractor([], $this->router);
        $this->assertEquals(
            [],
            $optionExtractorEmptyConfig->getOptionsForRequest(
                $this->getMock(ServerRequestInterface::class)
            )
        );
        $this->assertEquals(
            [],
            $optionExtractor->getOptionsForRequest(
                $this->getRequestMock(self::$url)
            )
        );
        /**
         * Test options exist with route match
         */
        $this->applyValidationConfig();
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $this->assertEquals(
            $this->config[0]['options'],
            $optionExtractor->getOptionsForRequest(
                $this->getRequestMock(self::$url)
            )
        );

        /**
         * Test options exist no route match
         */
        $this->assertEquals(
            $this->config[0]['options'],
            $optionExtractor->getOptionsForRequest(
                $this->getRequestMock('')
            )
        );
    }

    /**
     * @covers StdLib\Validator\OptionsExtractor::getAll
     */
    public function testGetAllOptions()
    {
        $optionExtractor = new OptionsExtractor($this->config, $this->router);

        $this->assertEquals(
            $this->config,
            $optionExtractor->getAll()
        );
    }

    /**
     * @covers StdLib\Validator\OptionsExtractor::getAllSanitize
     * @dataProvider sanitizeProvider
     */
    public function testAllSanitized($key, $value) {
        $this->applyValidationConfig();
        $optionExtractor = new OptionsExtractor($this->config, $this->router);
        $result = $optionExtractor->getAllSanitize();
        $this->assertEquals($result[0][$key], $value);
        $this->assertCount(3, $result[0]);
    }

    public function sanitizeProvider()
    {
        return [
            [
                'name', 'agreements'
            ],
            [
                'path', '/agreements[/{agreement_id}]'
            ],
            [
                'allowed_methods', ['GET', 'DELETE', 'PATCH', 'PUT', 'POST']
            ]
        ];
    }

    /**
     * @param $uriString
     * @param string $method
     * @return Request
     */
    private function getRequestMock($uriString, $method = 'GET')
    {

        $requestMock = $this->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();
        $uri = new Uri($uriString);
        $requestMock->expects($this->any())
        ->method('getUri')
        ->willReturnCallback(function () use ($uri) {
            return $uri;
        });
        $requestMock->expects($this->any())
        ->method('getMethod')
        ->willReturn($method);
        return $requestMock;
    }

    /**
     * Helper for applying the validation
     */
    private function applyValidationConfig()
    {
        $this->config[0]['options'] =  [
            'validation' =>  [
                '*' => GetAgreementValidatorRequest::class
            ]
        ];
    }
}