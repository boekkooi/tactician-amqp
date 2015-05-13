<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\AMQPCommand;
use Boekkooi\Tactician\AMQP\Transformer\EnvelopeTransformer;
use League\Tactician\Middleware;

/**
 * A middleware that will transform a AMQP command to a local command
 */
class EnvelopeTransformerMiddleware implements Middleware
{
    /**
     * @var EnvelopeTransformer
     */
    private $transformer;

    /**
     * @param EnvelopeTransformer $transformer
     */
    public function __construct(EnvelopeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof AMQPCommand) {
            $command = $this->transformer->transformEnvelopeToCommand($command->getEnvelope());
        } elseif ($command instanceof \AMQPEnvelope) {
            $command = $this->transformer->transformEnvelopeToCommand($command);
        }

        return $next($command);
    }
}
