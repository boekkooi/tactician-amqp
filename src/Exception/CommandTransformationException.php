<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\Message;

class CommandTransformationException extends \RuntimeException implements Exception
{
    /**
     * @var mixed
     */
    private $command;

    /**
     * Returns the command that failed to transform
     *
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $message
     * @param mixed $command
     * @return static
     */
    public static function invalidMessageFromTransformer($message, $command)
    {
        $exception = new static(sprintf(
            'A %s was expect to be returned by the Transformer for command %s but %s was received.',
            Message::class,
            (is_object($command) ? get_class($command) : gettype($command)),
            (is_object($message) ? get_class($message) : gettype($message))
        ));
        $exception->command = $command;

        return $exception;
    }
}
