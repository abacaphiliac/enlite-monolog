<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

class HandlerMock implements HandlerInterface
{

    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function isHandling(array $record): bool
    {
    }

    public function handle(array $record): bool
    {
    }

    public function handleBatch(array $records): void
    {
    }

    public function pushProcessor($callback)
    {
    }

    public function popProcessor()
    {
    }

    public function setFormatter(FormatterInterface $formatter)
    {
    }

    public function getFormatter()
    {
    }

    /**
     * Closes the handler.
     *
     * Ends a log cycle and frees all resources used by the handler.
     *
     * Closing a Handler means flushing all buffers and freeing any open resources/handles.
     *
     * Implementations have to be idempotent (i.e. it should be possible to call close several times without breakage)
     * and ideally handlers should be able to reopen themselves on handle() after they have been closed.
     *
     * This is useful at the end of a request and will be called automatically when the object
     * is destroyed if you extend Monolog\Handler\Handler.
     *
     * If you are thinking of calling this method yourself, most likely you should be
     * calling ResettableInterface::reset instead. Have a look.
     */
    public function close(): void
    {
        // TODO: Implement close() method.
    }
}
