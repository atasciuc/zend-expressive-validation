<?php
namespace ExpressiveValidator\Test\Factories;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase;
use ExpressiveValidator\Validator\OptionExtractorFactory;
use ExpressiveValidator\Validator\OptionsExtractor;
use Zend\Expressive\Router\RouterInterface;

class OptionsExtractorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testInvokation()
    {
        $container = $this->getMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
        ->willReturnCallback(function ($name) {
            if ($name === RouterInterface::class) {
                return $this->getMock(RouterInterface::class);
            } else {
                return [
                    'routes' => []
                ];
            }
        });

        $optionExtractorFactory = new OptionExtractorFactory();
        $optionExtractor = $optionExtractorFactory->__invoke($container);
        $this->assertInstanceOf(OptionsExtractor::class, $optionExtractor);
    }
}