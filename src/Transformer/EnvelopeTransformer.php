<?php
namespace Boekkooi\Tactician\AMQP\Transformer;

/**
 * A transformer that transforms a @see \AMQPEnvelope instance into a command
 */
interface EnvelopeTransformer
{
    /**
     * Returns a Command based on the provided envelope
     *
     * @param \AMQPEnvelope $envelope The envelope to transform
     * @return mixed
     */
    public function transformEnvelopeToCommand(\AMQPEnvelope $envelope);
}
