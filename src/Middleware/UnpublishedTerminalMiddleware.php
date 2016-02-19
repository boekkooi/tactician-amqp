<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Exception\CommandNotPublishedException;
use League\Tactician\Middleware;

/**
 * A middleware that will ways throw a exception.
 * This is very useful for detecting Commands that we not published/transformed or registered correctly.
 */
class UnpublishedTerminalMiddleware implements Middleware
{
    /**
     * @inheritdoc
     */
    public function execute($command, callable $next)
    {
        throw CommandNotPublishedException::forCommand(
            $command
        );
    }
}
