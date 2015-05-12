<?php
namespace Boekkooi\Tactician\AMQP;

use League\Tactician\Middleware;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ExchangeMiddleware implements Middleware
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

        if ($command instanceof \AMQPEnvelope) {
            $command = new AMQPCommand($command, null);
        }

        return $next($command);
    }
}
