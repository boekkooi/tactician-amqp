<?php
namespace Boekkooi\Tactician\AMQP\Publisher\Locator;

use Boekkooi\Tactician\AMQP\Exception\MissingPublisherException;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;

/**
 * Publisher locator for message objects
 *
 * This interface is often a wrapper around your frameworks dependency
 * injection container or just maps command names to a publisher.
 */
interface PublisherLocator
{
    /**
     * Retrieves the publisher for a specified message
     *
     * @param Message $message
     *
     * @return Publisher
     *
     * @throws MissingPublisherException
     */
    public function getPublisherForMessage(Message $message);
}
