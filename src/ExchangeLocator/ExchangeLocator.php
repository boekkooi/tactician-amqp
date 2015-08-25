<?php
namespace Boekkooi\Tactician\AMQP\ExchangeLocator;

use Boekkooi\Tactician\AMQP\Exception\MissingExchangeException;
use Boekkooi\Tactician\AMQP\Message;

/**
 * Exchange locator for message objects
 *
 * This interface is often a wrapper around your frameworks dependency
 * injection container or just maps command names to exchanges.
 */
interface ExchangeLocator
{
    /**
     * Retrieves the exchange for a specified message
     *
     * @param Message $message
     *
     * @return \AMQPExchange
     *
     * @throws MissingExchangeException
     */
    public function getExchangeForMessage(Message $message);
}
