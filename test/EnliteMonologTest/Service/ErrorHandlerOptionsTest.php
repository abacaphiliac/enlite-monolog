<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\ErrorHandlerOptions;
use Monolog\ErrorHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use function array_values;
use function exec;
use function set_error_handler;
use function stripos;
use function trigger_error;
use const E_ERROR;
use const E_USER_ERROR;

/**
 * @covers \EnliteMonolog\Service\ErrorHandlerOptions
 */
class ErrorHandlerOptionsTest extends TestCase
{
    /** @var ErrorHandlerOptions */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ErrorHandlerOptions();
    }

    public function testGetErrorLevelMap()
    {
        self::assertNull($this->sut->getErrorLevelMap());

        $this->sut->setErrorLevelMap([
            E_ERROR => 'critical',
        ]);

        self::assertSame([E_ERROR => 'critical'], $this->sut->getErrorLevelMap());
    }

    public function testDisableErrorHandler()
    {
        $this->sut->setErrorLevelMap(null);

        self::assertNull($this->sut->getErrorLevelMap());
    }

    public function testGetExceptionLevel()
    {
        self::assertNull($this->sut->getExceptionLevel());

        $this->sut->setExceptionLevel(LogLevel::ERROR);

        self::assertSame(LogLevel::ERROR, $this->sut->getExceptionLevel());
    }

    public function testDisableExceptionHandlerByLevel()
    {
        $this->sut->setExceptionLevelMap(null);

        self::assertNull($this->sut->getExceptionLevel());
    }

    public function testGetExceptionLevelMap()
    {
        self::assertNull($this->sut->getExceptionLevelMap());

        $this->sut->setExceptionLevelMap([
            E_ERROR => 'critical',
        ]);

        self::assertSame([E_ERROR => 'critical'], $this->sut->getExceptionLevelMap());
    }

    public function testDisableExceptionHandlerByLevelMap()
    {
        $this->sut->setExceptionLevelMap(null);

        self::assertNull($this->sut->getExceptionLevelMap());
    }

    public function testGetFatalLevel()
    {
        self::assertNull($this->sut->getFatalLevel());

        $this->sut->setFatalLevel('critical');

        self::assertSame('critical', $this->sut->getFatalLevel());
    }

    public function testDisableFatalHandler()
    {
        $this->sut->setFatalLevel(null);

        self::assertNull($this->sut->getFatalLevel());
    }

    public function testRegistersErrorHandler()
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            self::markTestSkipped('Class "\Monolog\ErrorHandler" is required.');
        }

        $oldErrorHandler = set_error_handler($prevHandler = function () {
        });

        $testHandler = new TestHandler();

        $options = new ErrorHandlerOptions([
            'error_level_map' => [
                E_USER_ERROR => LogLevel::ERROR,
            ],
        ]);

        $newErrorHandler = $options->registerLoggerAsErrorHandler(new Logger(self::class, [
            $testHandler,
        ]));

        self::assertInstanceOf(ErrorHandler::class, $newErrorHandler);

        self::assertFalse($testHandler->hasErrorRecords());

        try {
            trigger_error('whoops', E_USER_ERROR);
        } finally {
            // restore previous handler
            set_error_handler($oldErrorHandler);
        }

        self::assertTrue($testHandler->hasError('E_USER_ERROR: whoops'));
    }

    public function testRegistersExceptionHandler()
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            self::markTestSkipped('Class "\Monolog\ErrorHandler" is required.');
        }

        exec('/usr/bin/env php ' . __DIR__ . '/uncaughtException.php', $output);

        $lines = array_values(array_filter($output, function ($line) {
            return stripos($line, 'uncaughtException.php.ERROR: Uncaught exception') === 0;
        }));

        self::assertCount(1, $lines);
        self::assertStringContainsString('RuntimeException: ', $lines[0]);
        self::assertStringContainsString('whoops', $lines[0]);
    }

    public function testRegistersFatalErrorHandler()
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            self::markTestSkipped('Class "\Monolog\ErrorHandler" is required.');
        }

        exec('/usr/bin/env php ' . __DIR__ . '/fatalError.php 2>/dev/null', $output);

        $lines = array_values(array_filter($output, function ($line) {
            return stripos($line, 'fatalError.php.ALERT: Fatal Error (E_ERROR): Uncaught Error:') === 0;
        }));

        self::assertCount(1, $lines);
        self::assertStringContainsString("Class 'FakeClass' not found", $lines[0]);
    }
}
