<?php
namespace Boekkooi\Tactician\AMQP;

/**
 * A interface representing a message that needs to be published to a exchange.
 *
 * @see \AMQPExchange::publish
 */
interface Message
{
    /**
     * Returns the message content
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns the routing key
     *
     * @return null|string
     */
    public function getRoutingKey();

    /**
     * Return the flags
     * This can be AMQP_MANDATORY and/or AMQP_IMMEDIATE.
     *
     * @return int
     */
    public function getFlags();

    /**
     * Return the attributes
     *
     * This array can contain the following keys:
     * content_type, content_encoding, message_id, user_id,
     * app_id, delivery_mode, priority, timestamp,
     * expiration, type, reply_to, headers
     *
     * @return array
     */
    public function getAttributes();
}
