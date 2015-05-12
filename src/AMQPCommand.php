<?php
namespace Boekkooi\Tactician\AMQP;

/**
 * Indicates the command was received from a AMQPQueue
 *
 * @final
 */
class AMQPCommand
{
    /**
     * @var \AMQPEnvelope
     */
    private $envelope;

    /**
     * @param \AMQPEnvelope $envelope
     */
    public function __construct(\AMQPEnvelope $envelope)
    {
        $this->envelope = $envelope;
    }

    /**
     * Returns the wrapped envelope
     *
     * @return \AMQPEnvelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }
}
