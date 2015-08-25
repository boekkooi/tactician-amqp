<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\Message;

class MissingPublisherException extends \OutOfBoundsException implements Exception
{
    /**
     * @var Message
     */
    protected $tacticianMessage;

    /**
     * @return Message
     */
    public function getTacticianMessage()
    {
        return $this->tacticianMessage;
    }

    /**
     * @param Message $message
     *
     * @return static
     */
    public static function forMessage(Message $message)
    {
        $exception = new static('Missing publisher for message of class ' . get_class($message));
        $exception->tacticianMessage = $message;

        return $exception;
    }
}
