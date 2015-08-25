<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Command;
use League\Tactician\Middleware;

/**
 * A middleware that will ack or reject a AMQP command envelope
 */
class ConsumeMiddleware implements Middleware
{
    /**
     * @var bool
     */
    private $requeue;

    public function __construct($requeueOnException = true)
    {
        $this->requeue = $requeueOnException;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if (!$command instanceof Command) {
            return $next($command);
        }

        $queue = $command->getQueue();
        $deliveryTag = $command->getEnvelope()->getDeliveryTag();

        try {
            $res = $next($command);
            $queue->ack($deliveryTag);

            return $res;
        } catch (\Exception $e) {
            $queue->reject(
                $deliveryTag,
                ($this->requeue ? AMQP_REQUEUE : AMQP_NOPARAM)
            );

            throw $e;
        }
    }
}
