<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;
use Boekkooi\Tactician\AMQP\Exception\NoResponseException;
use Boekkooi\Tactician\AMQP\ExchangeLocator\ExchangeLocator;
use Boekkooi\Tactician\AMQP\Message;

/**
 * A RPC command publisher
 */
class RemoteProcedureCommandPublisher extends ExchangeLocatorPublisher
{
    /**
     * @var int
     */
    private $responseTimeout;
    /**
     * @var int
     */
    private $responseWaitInterval;
    /**
     * @var int|null
     */
    private $queueTimeout;

    /**
     * @param ExchangeLocator $exchangeLocator
     * @param int $responseTimeout The time to wait for a response in millisecond
     * @param int|null $queueTimeout The time before the amqp server removes the response queue in millisecond
     * @param int $waitInterval The interval between response checks in millisecond
     */
    public function __construct(ExchangeLocator $exchangeLocator, $responseTimeout = 1000, $queueTimeout = null, $waitInterval = 10)
    {
        parent::__construct($exchangeLocator);

        $this->responseTimeout = $responseTimeout / 1000;
        $this->queueTimeout = $queueTimeout;
        $this->responseWaitInterval = $waitInterval * 1000;
    }

    /**
     * @inheritdoc
     */
    protected function publishToExchange(Message $message, \AMQPExchange $exchange)
    {
        // Create a response queue
        $queue = $this->declareResponseQueue($exchange);

        try {
            $attributes = (array)$message->getAttributes();

            // Setup correlation id
            $correlationId = isset($attributes['correlation_id']) ? $attributes['correlation_id'] : uniqid();

            // Patch attributes
            $attributes['reply_to'] = $queue->getName();
            $attributes['correlation_id'] = $correlationId;

            // Publish the message
            $isPublished = $exchange->publish(
                $message->getMessage(),
                $message->getRoutingKey(),
                $message->getFlags(),
                $attributes
            );
            if (!$isPublished) {
                throw FailedToPublishException::fromMessage($message);
            }

            // Get the response
            $response = $this->waitForResponse($queue, $correlationId);
            if (!$response instanceof \AMQPEnvelope) {
                throw NoResponseException::forMessage($message);
            }

            return $response;
        } finally {
            // Cleanup
            $this->cleanupResponseQueue($queue);
        }
    }

    /**
     * @param \AMQPExchange $exchange
     * @return \AMQPQueue
     */
    protected function declareResponseQueue(\AMQPExchange $exchange)
    {
        $queue = new \AMQPQueue($exchange->getChannel());
        $queue->setFlags(AMQP_EXCLUSIVE);
        if ($this->queueTimeout !== null) {
            $queue->setArgument("x-expires", $this->queueTimeout);
        }
        $queue->declareQueue();

        return $queue;
    }

    /**
     * @param \AMQPQueue $queue
     * @param string $correlationId
     * @return \AMQPEnvelope|bool
     */
    protected function waitForResponse(\AMQPQueue $queue, $correlationId)
    {
        $time = microtime(true);
        $timeout = $time + $this->responseTimeout;
        while ($time < $timeout) {
            $envelope = $queue->get(AMQP_AUTOACK);
            if ($envelope === false) {
                usleep($this->responseWaitInterval);
                $time = microtime(true);
                continue;
            }

            if ($envelope->getCorrelationId() !== $correlationId) {
                // This is actually only useful when you override declareResponseQueue and cleanupResponseQueue
                // You can override theses to use only a single queue for all rpc response calls
                // This is still implemented to because I'm a sucker for FC ;)
                continue;
            }

            return $envelope;
        }

        return false;
    }

    /**
     * @param \AMQPQueue $queue
     */
    protected function cleanupResponseQueue(\AMQPQueue $queue)
    {
        $queue->delete();
    }
}
