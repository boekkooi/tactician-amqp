<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\Message;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class FailedToPublishException extends \RuntimeException implements Exception
{
    /**
     * @var Message
     */
    private $tacticianMessage;

    /**
     * @param Message $message
     *
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        $exception = new static('Failed to publish a message to it\'s exchange');
        $exception->tacticianMessage = $message;

        return $exception;
    }

    /**
     * @param \AMQPException $exception
     * @param Message $message
     *
     * @return static
     */
    public static function fromException(\AMQPException $exception, Message $message)
    {
        $exception = new static('A AMQP exception occured while publishing a message', 0, $exception);
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
