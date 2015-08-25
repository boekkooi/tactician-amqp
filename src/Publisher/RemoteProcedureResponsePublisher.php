<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Command;
use Boekkooi\Tactician\AMQP\Exception\InvalidArgumentException;
use Boekkooi\Tactician\AMQP\Message;

/**
 * A publisher for a RPC response.
 */
class RemoteProcedureResponsePublisher extends ExchangePublisher
{
    /**
     * @var \AMQPChannel
     */
    private $channel;
    /**
     * @var string
     */
    private $routingKey;
    /**
     * @var string
     */
    private $correlationId;

    public function __construct(Command $command)
    {
        if (empty($command->getEnvelope()->getReplyTo())) {
            throw InvalidArgumentException::forMissingCommandReplyTo($command);
        }

        $this->channel = $command->getQueue()->getChannel();
        $this->routingKey = $command->getEnvelope()->getReplyTo();
        $this->correlationId = $command->getEnvelope()->getCorrelationId();
    }

    protected function publishToExchange(Message $message, \AMQPExchange $exchange)
    {
        $attributes = (array)$message->getAttributes();
        $attributes['correlation_id'] = $this->correlationId;

        return $exchange->publish(
            $message->getMessage(),
            $this->routingKey,
            $message->getFlags(),
            $attributes
        );
    }

    /**
     * @inheritdoc
     */
    protected function getExchange(Message $message)
    {
        return new \AMQPExchange($this->channel);
    }
}
