<?php

namespace EnliteMonolog\Service;

use Monolog\ErrorHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Zend\Stdlib\AbstractOptions;

class ErrorHandlerOptions extends AbstractOptions
{
    /**
     * @var array|null an array of E_* constant to LogLevel::* constant mapping, or null to disable error handling
     */
    private $errorLevelMap = null;

    /**
     * @var string|null a LogLevel::* constant, or false to disable exception handling
     */
    private $exceptionLevel = null;

    /**
     * @var array|null an array of class name to LogLevel::* constant mapping, or false to disable exception handling
     */
    private $exceptionLevelMap = null;

    /**
     * @var string|null a LogLevel::* constant, or false to disable fatal error handling
     */
    private $fatalLevel = null;

    public function getErrorLevelMap(): ?array
    {
        return $this->errorLevelMap;
    }

    public function setErrorLevelMap(?array $errorLevelMap): void
    {
        $this->errorLevelMap = $errorLevelMap;
    }

    public function getExceptionLevel(): ?string
    {
        return $this->exceptionLevel;
    }

    public function setExceptionLevel(?string $exceptionLevel): void
    {
        $this->exceptionLevel = $exceptionLevel;
    }

    public function getExceptionLevelMap(): ?array
    {
        return $this->exceptionLevelMap;
    }

    public function setExceptionLevelMap(?array $exceptionLevelMap): void
    {
        $this->exceptionLevelMap = $exceptionLevelMap;
    }

    public function getFatalLevel(): ?string
    {
        return $this->fatalLevel;
    }

    public function setFatalLevel(?string $fatalLevel): void
    {
        $this->fatalLevel = $fatalLevel;
    }

    public function registerLoggerAsErrorHandler(LoggerInterface $logger): ?ErrorHandler
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            return null;
        }

        if (defined('Monolog\Logger::API') && Logger::API === 2) {
            return ErrorHandler::register(
                $logger,
                $this->getErrorLevelMap() ?? false,
                $this->getExceptionLevelMap() ?? false,
                $this->getFatalLevel() ?? false
            );
        }

        return ErrorHandler::register(
            $logger,
            $this->getErrorLevelMap() ?? false,
            $this->getExceptionLevel() ?? false,
            $this->getFatalLevel() ?? false
        );
    }
}
