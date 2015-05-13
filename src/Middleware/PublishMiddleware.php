<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Message;
use League\Tactician\Middleware;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;

/**
 * A middleware that will publish AMQP message to a publisher
 */
class PublishMiddleware implements Middleware
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof Message) {
            return $this->publisher->publish($command);
        }

        return $next($command);
    }
}
