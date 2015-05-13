<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\Message;

class FailedToPublishException extends \RuntimeException implements Exception
{
    /**
     * @var Message
     */
    protected $tacticianMessage;

    /**
     * @param Message $message
     *
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        $exception = new static('Failed to publish the message to it\'s exchange');
        $exception->tacticianMessage = $message;

        return $exception;
    }

    /**
     * @param \AMQPException $exception
     * @param Message $message
     *
     * @return static
     */
    public static function fromException(Message $message, \AMQPException $exception)
    {
        $exception = new static('A AMQP exception occured while publishing the message', 0, $exception);
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
