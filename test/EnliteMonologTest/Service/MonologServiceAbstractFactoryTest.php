<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;


use EnliteMonolog\Service\MonologServiceAbstractFactory;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceAbstractFactory
 */
class MonologServiceAbstractFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetConfigWithNoneConfig()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('config', array());

        $this->assertEquals(array(), $factory->getConfig($serviceLocator));
    }

    public function testGetConfigWithConfig()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                    'EnliteMonolog' => array()
                 )
            )
        );

        $this->assertEquals(array('EnliteMonolog' => array()), $factory->getConfig($serviceLocator));
    }

    public function testGetConfigWithAlreadyFetchConfig()
    {
        $factory = new MonologServiceAbstractFactory();
        $factory->setConfig(array('a' => 'b'));
        $serviceLocator = new ServiceManager();


        $this->assertEquals(array('a' => 'b'), $factory->getConfig($serviceLocator));
    }

    public function testCanCreateServiceWithName()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                     'default' => array()
                 )
            )
        );
        $this->assertTrue($factory->canCreateServiceWithName($serviceLocator, 'default', 'default'));
        $this->assertFalse($factory->canCreateServiceWithName($serviceLocator, 'test', 'test'));
    }

    public function testCreateServiceWithName()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                     'default' => array()
                 )
            )
        );

        $actual = $factory->createServiceWithName($serviceLocator, 'default', 'default');

        self::assertInstanceOf('\Psr\Log\LoggerInterface', $actual);
    }

    public function testCanCreate()
    {
        $serviceLocator = new ServiceManager();
        if (!(interface_exists('\Interop\Container\ContainerInterface')
            && $serviceLocator instanceof ContainerInterface)) {
            self::markTestSkipped('Upgrade dependencies to execute this test.');
        }

        $factory = new MonologServiceAbstractFactory();
        $serviceLocator->setService(
            'config',
            array(
                'EnliteMonolog' => array(
                    'default' => array()
                )
            )
        );
        $this->assertTrue($factory->canCreate($serviceLocator, 'default'));
        $this->assertFalse($factory->canCreate($serviceLocator, 'test'));
    }

    public function testInvoke()
    {
        $serviceLocator = new ServiceManager();
        if (!(interface_exists('\Interop\Container\ContainerInterface')
            && $serviceLocator instanceof ContainerInterface)) {
            self::markTestSkipped('Upgrade dependencies to execute this test.');
        }

        $factory = new MonologServiceAbstractFactory();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                     'default' => array()
                 )
            )
        );

        $actual = $factory($serviceLocator, 'default');

        self::assertInstanceOf('\Psr\Log\LoggerInterface', $actual);
    }
}
