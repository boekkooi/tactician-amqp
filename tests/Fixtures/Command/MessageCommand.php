<?php
namespace Tests\Boekkooi\Tactician\AMQP\Fixtures\Command;

use Boekkooi\Tactician\AMQP\Message;

class MessageCommand implements Message
{
    private $message;
    private $routingKey;
    private $flags;
    private $attributes;

    public function __construct($message, $routingKey = null, $flags = AMQP_IMMEDIATE, array $attributes = [])
    {
        $this->message = $message;
        $this->routingKey = $routingKey;
        $this->flags = $flags;
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutingKey()
    {
        $this->routingKey;
    }

    /**
     * Return the flags
     * This can be \AMQP_MANDATORY and/or \AMQP_IMMEDIATE.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
