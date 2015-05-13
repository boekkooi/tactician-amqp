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
     * @var \AMQPQueue
     */
    private $queue;

    /**
     * @param \AMQPEnvelope $envelope
     * @param \AMQPQueue $queue
     */
    public function __construct(\AMQPEnvelope $envelope, \AMQPQueue $queue)
    {
        $this->envelope = $envelope;
        $this->queue = $queue;
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

    /**
     * Returns the queue from which the envelope was acquired
     *
     * @return \AMQPQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
