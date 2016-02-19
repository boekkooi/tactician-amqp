<?php
namespace Boekkooi\Tactician\AMQP\Exception;

class CommandNotPublishedException extends \RuntimeException implements Exception
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

    public static function forCommand($command)
    {
        $exception = new self(sprintf(
            'Command of type %s was expected to be published/handled but this did not occur.'.
            ' Did you forget to register the command for publication?',
            is_object($command) ? get_class($command) : gettype($command)
        ));
        $exception->command = $command;

        return $exception;
    }
}
