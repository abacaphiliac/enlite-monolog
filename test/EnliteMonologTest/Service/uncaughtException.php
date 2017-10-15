<?php

use EnliteMonolog\Service\ErrorHandlerOptions;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

include_once __DIR__ . '/../../../vendor/autoload.php';

$handler = new StreamHandler(STDOUT);
$handler->setFormatter(new LineFormatter(
    '%channel%.%level_name%: %message% %context%'
));

$logger = new Logger(basename(__FILE__), [
    $handler,
]);

$options = new ErrorHandlerOptions([
    'exception_level' => LogLevel::ERROR,
    'exception_level_map' => [
        RuntimeException::class => LogLevel::ERROR,
    ],
]);

$options->registerLoggerAsErrorHandler($logger);

throw new RuntimeException('whoops');
