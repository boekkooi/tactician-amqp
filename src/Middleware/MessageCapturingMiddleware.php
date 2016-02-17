<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\MessageCapturer;
use Boekkooi\Tactician\AMQP\Publisher\Locator\PublisherLocator;
use League\Tactician\Middleware;

/**
 * A middleware that will publish any message and store it in memory
 */
class MessageCapturingMiddleware implements Middleware
{
    /**
     * @var MessageCapturer
     */
    private $capturer;

    /**
     * @param MessageCapturer $capturer
     */
    public function __construct(MessageCapturer $capturer)
    {
        $this->capturer = $capturer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if (!$command instanceof Message) {
            return $next($command);
        }

        $this->capturer->publish($command);

        return null;
    }
}
