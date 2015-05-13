<?php
namespace Boekkooi\Tactician\AMQP;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface AMQPAwareMessage extends Message
{
    /**
     * Returns the vhost name to publish the message to
     *
     * @return string
     */
    public function getVHost();

    /**
     * Returns the exchange name to publish the message to
     *
     * @return string
     */
    public function getExchange();
}
