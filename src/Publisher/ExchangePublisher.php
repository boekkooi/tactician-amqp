<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;
use Boekkooi\Tactician\AMQP\Exception\MissingExchangeException;
use Boekkooi\Tactician\AMQP\Message;

/**
 * A base exchange message publisher
 */
abstract class ExchangePublisher implements Publisher
{
    /**
     * {@inheritdoc}
     */
    public function publish(Message $message)
    {
        $exchange = $this->getExchange($message);
        if (!$exchange instanceof \AMQPExchange) {
            throw MissingExchangeException::forMessage($message);
        }

        try {
            $isPublished = $this->publishToExchange($message, $exchange);

            if (!$isPublished) {
                throw FailedToPublishException::fromMessage($message);
            }
        } catch (\AMQPException $e) {
            throw FailedToPublishException::fromException($message, $e);
        }
    }

    /**
     * Publish the message to AMQP exchange
     *
     * @param Message $message
     * @param \AMQPExchange $exchange
     * @return bool
     * @throws \AMQPException
     */
    protected function publishToExchange(Message $message, \AMQPExchange $exchange)
    {
        return $exchange->publish(
            $message->getMessage(),
            $message->getRoutingKey(),
            $message->getFlags(),
            $message->getAttributes()
        );
    }

    /**
     * Returns the exchange for a given message
     *
     * @param Message $message
     *
     * @throws MissingExchangeException
     *
     * @return \AMQPExchange
     */
    abstract protected function getExchange(Message $message);
}
