<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologOptions;
use EnliteMonolog\Service\MonologServiceFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceFactory
 */
class MonologServiceFactoryTest extends TestCase
{

    public function testCreateService()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $factory = new MonologServiceFactory();

        $service = $factory->createService($serviceManager);
        $this->assertInstanceOf('Monolog\Logger', $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf('Monolog\Handler\TestHandler', $service->popHandler());
    }

    public function testCreateHandlerFromServiceLocator()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('TestHandler', 'works');

        $factory = new MonologServiceFactory();

        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), 'TestHandler');
        $this->assertEquals('works', $handler);
    }

    public function testCreateHandlerByClassNameWithoutArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $handler = array('name' => 'Monolog\Handler\TestHandler');

        $factory = new MonologServiceFactory();
    
        $actual = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf('Monolog\Handler\TestHandler', $actual);
    }

    public function testCreateHandlerByClassNameWithArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();

        $handler = array('name' => 'EnliteMonologTest\Service\HandlerMock', 'args' => array('some.log'));

        $factory = new MonologServiceFactory();

        /** @var HandlerMock $logger */
        $logger = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf('EnliteMonologTest\Service\HandlerMock', $logger);
        $this->assertEquals('some.log', $logger->getPath());
    }

    public function testCreateHandlerByEmptyClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), array());
    }

    public function testCreateHandlerNotExistsClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), array('name' => 'unknown_class_name'));
    }

    public function testCreateHandlerWithBadArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => 'Monolog\Handler\TestHandler',
            'args' => ''
        ));
    }

    public function testCreateProcessorFromAnonymousFunction()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, $expected = function () {
        });

        self::assertSame($expected, $actual);
    }

    public function testCreateProcessorFromServiceName()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('MyProcessor', $expected = function () {
        });

        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, 'MyProcessor');

        self::assertSame($expected, $actual);
    }

    public function testCreateProcessorFromClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, '\Monolog\Processor\MemoryUsageProcessor');

        self::assertInstanceOf('\Monolog\Processor\MemoryUsageProcessor', $actual);
    }

    public function testCreateProcessorNotExistsClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );

        $factory->createProcessor($serviceManager, '\stdClass');
    }

    public function testCreateNonCallableProcessorFromServiceName()
    {
        $services = new ServiceManager();
        $services->setService('\stdClass', new \stdClass());

        $sut = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );

        $sut->createProcessor($services, '\stdClass');
    }

    public function testCreateProcessorWithMissingProcessorNameConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(

        ));
    }

    public function testCreateProcessorNotExistsClassNameInNamePart()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(
            'name' => '\InvalidProcessorClassName',
        ));
    }

    public function testCreateProcessorWithInvalidProcessorArgumentConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(
            'name' => '\EnliteMonologTest\Service\ProcessorMock',
            'args' => 'MyArgs',
        ));
    }

    public function testCreateProcessorWithWrongNamedArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(
            'name' => '\EnliteMonologTest\Service\ProcessorMock',
            'args' => array(
                'notExisted' => 'test',
            ),
        ));
    }

    public function testCreateProcessorWithArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, array(
            'name' => '\EnliteMonologTest\Service\ProcessorMock',
            'args' => array(
                'test',
            ),
        ));

        self::assertInstanceOf('\EnliteMonologTest\Service\ProcessorMock', $actual);
    }

    public function testCreateProcessorWithoutArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, array(
            'name' => '\Monolog\Processor\MemoryUsageProcessor',
        ));

        self::assertInstanceOf('\Monolog\Processor\MemoryUsageProcessor', $actual);
    }

    public function testCreateProcessorWithNamedArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $argument = 'test';
        $actual = $factory->createProcessor($serviceManager, array(
            'name' => '\EnliteMonologTest\Service\ProcessorMock',
            'args' => array(
                'argument' => $argument,
            ),
        ));

        self::assertInstanceOf('\EnliteMonologTest\Service\ProcessorMock', $actual);
        $reflection = new \ReflectionClass($actual);
        $property = $reflection->getProperty('argument');
        $property->setAccessible(true);
        self::assertSame($argument, $property->getValue($actual), 'Unable to set arguments by name');
    }

    public function testCreateFormatterFromServiceName()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'MyFormatter',
            $expected = $this->getMockBuilder('\Monolog\Formatter\FormatterInterface')->getMock()
        );

        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, 'MyFormatter');

        self::assertSame($expected, $actual);
    }

    public function testCreateFormatterWithMissingFormatterNameConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createFormatter($serviceManager, array(

        ));
    }

    public function testCreateFormatterNotExistsClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createFormatter($serviceManager, array(
            'name' => '\InvalidFormatterClassName',
        ));
    }

    public function testCreateFormatterWithInvalidFormatterArgumentConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
            'args' => 'MyArgs',
        ));
    }

    public function testCreateFormatterWithArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
            'args' => array(
                'format' => LineFormatter::SIMPLE_FORMAT,
                'dateFormat' => 'Y-m-d H:i:s',
            ),
        ));

        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual);
    }

    public function testCreateFormatterWithoutArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
        ));

        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual);
    }

    public function testCreateFormatterWithNamedArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $dateFormat = 'Y-m-d\TH:i:sZ';
        $actual = $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
            'args' => array(
                'dateFormat' => $dateFormat,
            ),
        ));

        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual);
        $reflection = new \ReflectionClass($actual);
        $property = $reflection->getProperty('dateFormat');
        $property->setAccessible(true);
        self::assertSame($dateFormat, $property->getValue($actual), 'Unable to set arguments by name');
    }


    public function testCreateLoggerWithProcessor()
    {
        $options = new MonologOptions();
        $options->setProcessors(array(
            '\Monolog\Processor\MemoryUsageProcessor',
        ));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createLogger($serviceManager, $options);

        self::assertInstanceOf('\Monolog\Logger', $actual);
        self::assertInstanceOf('\Monolog\Processor\MemoryUsageProcessor', $actual->popProcessor());
    }

    public function testCreateHandlerWithFormatter()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createHandler($serviceManager, new MonologOptions(), array(
            'name' => '\Monolog\Handler\NullHandler',
            'formatter' => array(
                'name' => '\Monolog\Formatter\LineFormatter',
            ),
        ));

        self::assertInstanceOf('\Monolog\Handler\NullHandler', $actual);
        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual->getFormatter());
    }

    public function testCreateHandlerWithRandomOrderArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var \Monolog\Handler\TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => 'Monolog\Handler\TestHandler',
            'args' => array(
                'bubble' => false
            )
        ));

        self::assertFalse($handler->getBubble());
    }


    public function testHandlersOrder()
    {
        $config = array(
            'name' => 'test',
            'handlers' => array(
                'testHandler' => 'TestHandler',
                'nullHandler' => array('name' => 'Monolog\Handler\NullHandler'),
            )
        );

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var \Monolog\Handler\TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => 'Monolog\Handler\TestHandler',
            'args' => array(
                'bubble' => false
            )
        ));
        $serviceManager->setService('TestHandler', $handler);
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $service = $factory->createService($serviceManager);

        $service->addError('HandleThis!');

        $handler1 = $service->popHandler();
        $this->assertInstanceOf('Monolog\Handler\TestHandler', $handler1);

        $handler2 = $service->popHandler();
        $this->assertInstanceOf('Monolog\Handler\NullHandler', $handler2);

        self::assertTrue($handler->hasErrorRecords());
    }

    /**
     * @return FormatterInterface
     */
    public function testHandlerGetsDefaultFormatter()
    {
        $serviceManager = new ServiceManager();

        $monologOptions = new MonologOptions();

        $factory = new MonologServiceFactory();
        $handler = $factory->createHandler($serviceManager, $monologOptions, array(
            'name' => '\Monolog\Handler\NullHandler',
        ));

        $formatter = $handler->getFormatter();

        self::assertInstanceOf('\Monolog\Formatter\FormatterInterface', $formatter);

        return $formatter;
    }

    public function testHandlerGetsDefaultFormatterWithDefaultDateFormat()
    {
        $formatter = $this->testHandlerGetsDefaultFormatter();

        $line = $formatter->format(array(
            'datetime' => new \DateTime('2016-01-01 00:00:00', timezone_open('UTC')),
            'channel' => 'test',
            'level_name' => 'ERROR',
            'message' => 'foobar',
            'extra' => array(),
            'context' => array(),
        ));

        self::assertStringContainsString('[2016-01-01 00:00:00]', $line);
    }

    public function testInvoke()
    {
        $services = new ServiceManager();

        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $services->setService('EnliteMonologOptions', new MonologOptions($config));

        $sut = new MonologServiceFactory();

        $service = $sut(new ContainerMock($services), 'EnliteMonolog');
        $this->assertInstanceOf('Monolog\Logger', $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf('Monolog\Handler\TestHandler', $service->popHandler());
    }

    public function testCreateHandlerFromOptions()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $options = new MonologOptions();
        $options->setHandlers(array(
            'HandlerMock' => array(
                'name' => '\EnliteMonologTest\Service\HandlerMock',
                'args' => array(
                    'path' => '/FooBar',
                ),
            ),
        ));

        $result = $sut->createHandler($services, $options, array(
            'name' => '\EnliteMonologTest\Service\HandlerMock',
            'args' => array(
                'handler' => 'HandlerMock',
                'path' => '/FizBuz',
            ),
        ));

        self::assertInstanceOf('\EnliteMonologTest\Service\HandlerMock', $result);
    }

    public function testCreateHandlerWithInvalidArguments()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $options = new MonologOptions();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Handler(\EnliteMonologTest\Service\HandlerMock) has an invalid argument configuration'
        );

        $sut->createHandler($services, $options, array(
            'name' => '\EnliteMonologTest\Service\HandlerMock',
            'args' => array(),
        ));
    }

    public function testCreateFormatterWithMissingArguments()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Formatter(\EnliteMonologTest\Service\FormatterMock) has an invalid argument config'
        );

        $sut->createFormatter($services, array(
            'name' => '\EnliteMonologTest\Service\FormatterMock',
            'args' => array(),
        ));
    }

    public function testCreateFormatterWithInvalidArguments()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Formatter(\EnliteMonologTest\Service\FormatterMock) has an invalid argument config'
        );

        $sut->createFormatter($services, array(
            'name' => '\EnliteMonologTest\Service\FormatterMock',
            'args' => array(
                'NotEncoder' => new \stdClass(),
            ),
        ));
    }

    public function testCreateFormatterWithPrivateConstructor()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Formatter(\EnliteMonologTest\Service\FormatterPrivateConstructorMock) has an invalid'
        );

        $sut->createFormatter($services, array(
            'name' => '\EnliteMonologTest\Service\FormatterPrivateConstructorMock',
            'args' => array(),
        ));
    }

    public function testCreateFormatterFromStdClass()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $result = $sut->createFormatter($services, array(
            'name' => '\stdClass',
            'args' => array(),
        ));

        self::assertInstanceOf('\stdClass', $result);
    }
}
