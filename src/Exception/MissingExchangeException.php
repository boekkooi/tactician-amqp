<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\Message;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class MissingExchangeException extends \OutOfBoundsException implements Exception
{
    /**
     * @var string
     */
    private $tacticianMessage;

    /**
     * @param Message $message
     *
     * @return static
     */
    public static function forMessage(Message $message)
    {
        $exception = new static('Missing exchange for message of class ' . get_class($message));
        $exception->tacticianMessage = $message;

        return $exception;
    }

    /**
     * @return Message
     */
    public function getTacticianMessage()
    {
        return $this->tacticianMessage;
    }
}
