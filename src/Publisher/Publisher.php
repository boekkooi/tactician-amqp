<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface Publisher
{
    /**
     * Publish a message to a AMQP exchange.
     *
     * @param Message $message
     *
     * @throws FailedToPublishException
     *
     * @return mixed Should be void but is mixed to support RPC
     */
    public function publish(Message $message);
}
