<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Command;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Boekkooi\Tactician\AMQP\Publisher\RemoteProcedureResponsePublisher;
use Boekkooi\Tactician\AMQP\Transformer\ResponseTransformer;
use League\Tactician\Middleware;

/**
 * A middleware that will handle a AMQP RPC (Remote Procedure Call) command/envelope response
 */
class RPCMiddleware implements Middleware
{
    /**
     * @var ResponseTransformer
     */
    private $transformer;

    public function __construct(ResponseTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        // Check that the command expects a response
        if (!$command instanceof Command || empty($command->getEnvelope()->getReplyTo())) {
            return $next($command);
        }

        // Execute command
        $result = $next($command);

        // Transform result
        $message = $this->transformer->transformCommandResponse($result);

        // Publish the response message
        $publisher = $this->getPublisher($command);
        $publisher->publish($message);

        return $result;
    }

    /**
     * Get a publisher for the provided command
     *
     * @param Command $command
     * @return Publisher
     */
    protected function getPublisher(Command $command)
    {
        return new RemoteProcedureResponsePublisher($command);
    }
}
