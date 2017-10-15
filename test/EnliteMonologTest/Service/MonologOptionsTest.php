<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologOptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use const E_ERROR;

/**
 * @covers \EnliteMonolog\Service\MonologOptions
 */
class MonologOptionsTest extends TestCase
{
    /** @var MonologOptions */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new MonologOptions();
    }
    
    public function testGetDefaultName()
    {
        self::assertEquals('EnliteMonolog', $this->sut->getName());
    }
    
    public function testSetName()
    {
        $this->sut->setName($expected = 'MyLogger');
        
        self::assertEquals($expected, $this->sut->getName());
    }
    
    public function testGetDefaultHandlers()
    {
        $actual = $this->sut->getHandlers();

        self::assertInternalType('array', $actual);
        self::assertCount(0, $actual);
    }
    
    public function testSetHandlers()
    {
        $this->sut->setHandlers(array(
            $expected = 'MyHandlerService',
        ));

        self::assertInternalType('array', $this->sut->getHandlers());
        self::assertContains($expected, $this->sut->getHandlers());
    }
    
    public function testGetDefaultProcessors()
    {
        $actual = $this->sut->getProcessors();

        self::assertInternalType('array', $actual);
        self::assertCount(0, $actual);
    }
    
    public function testSetProcessors()
    {
        $this->sut->setProcessors(array(
            $expected = 'MyProcessorService',
        ));

        self::assertInternalType('array', $this->sut->getProcessors());
        self::assertContains($expected, $this->sut->getProcessors());
    }

    public function testErrorHandlerOptions()
    {
        $options = new MonologOptions();

        self::assertNull($options->getErrorHandlerOptions());

        $options->setErrorHandlerOptions([
        ]);

        self::assertNull($options->getErrorHandlerOptions()->getErrorLevelMap());
        self::assertNull($options->getErrorHandlerOptions()->getExceptionLevel());
        self::assertNull($options->getErrorHandlerOptions()->getExceptionLevelMap());
        self::assertNull($options->getErrorHandlerOptions()->getFatalLevel());

        $options->setErrorHandlerOptions([
            'error_level_map' => [
                E_ERROR => LogLevel::ERROR,
            ],
            'exception_level' => LogLevel::ERROR,
            'exception_level_map' => [
                E_ERROR => LogLevel::ERROR,
            ],
            'fatal_level' => LogLevel::ERROR,
        ]);

        self::assertSame([E_ERROR => LogLevel::ERROR], $options->getErrorHandlerOptions()->getErrorLevelMap());
        self::assertSame(LogLevel::ERROR, $options->getErrorHandlerOptions()->getExceptionLevel());
        self::assertSame([E_ERROR => LogLevel::ERROR], $options->getErrorHandlerOptions()->getExceptionLevelMap());
        self::assertSame(LogLevel::ERROR, $options->getErrorHandlerOptions()->getFatalLevel());
    }
}
