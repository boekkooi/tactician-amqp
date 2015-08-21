<?php
namespace Boekkooi\Tactician\AMQP\Transformer;

/**
 * A command response transformer.
 * Used for transforming responses of a Remote Procedure Call command in @see \Boekkooi\Tactician\AMQP\Middleware\RPCMiddleware
 */
interface ResponseTransformer
{
    /**
     * Returns a @see \Boekkooi\Tactician\AMQP\Message based on the provided data
     *
     * @param mixed $data The data to transform
     * @return \Boekkooi\Tactician\AMQP\Message A message representing the data
     */
    public function transformCommandResponse($data);
}
