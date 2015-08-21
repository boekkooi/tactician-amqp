<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Exception\CommandTransformationException;
use Boekkooi\Tactician\AMQP\Transformer\CommandTransformer;
use Boekkooi\Tactician\AMQP\Message;
use League\Tactician\Middleware;

/**
 * A middleware to transform a command to a AMQP Message.
 * This middleware should to be used before @see \Boekkooi\Tactician\AMQP\ExchangeMiddleware
 */
class CommandTransformerMiddleware implements Middleware
{
    /**
     * @var string[]
     */
    private $commands;

    /**
     * @var CommandTransformer
     */
    private $transformer;

    /**
     * @param CommandTransformer $transformer
     * @param string[] $commands A array of commands that are supported
     */
    public function __construct(CommandTransformer $transformer, array $commands = [])
    {
        $this->commands = $commands;
        $this->transformer = $transformer;
    }

    /**
     * Add a supported command class
     *
     * @param string $commandClass
     */
    public function addSupportedCommand($commandClass)
    {
        $this->commands[] = ltrim($commandClass, '\\');
    }

    /**
     * Add a set of supported command classes
     *
     * @param array $commandClasses
     */
    public function addSupportedCommands(array $commandClasses)
    {
        foreach ($commandClasses as $commandClass) {
            $this->addSupportedCommand($commandClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if (!is_object($command) || !in_array(get_class($command), $this->commands, true)) {
            return $next($command);
        }

        $message = $this->transformer->transformCommandToMessage($command);
        if (!$message instanceof Message) {
            throw CommandTransformationException::invalidMessageFromTransformer($message, $command);
        }

        return $next($message);
    }

    /**
     * Returns all supported command class names
     *
     * @return \string[]
     */
    public function getSupportedCommands()
    {
        return $this->commands;
    }
}
