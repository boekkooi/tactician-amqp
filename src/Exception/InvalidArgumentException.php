<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\AMQPCommand;

class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
    public static function forMissingCommandReplyTo(AMQPCommand $command)
    {
        return new static(sprintf(
            'Expect command envelope to have a reply-to but "%s" was found',
            $command->getEnvelope()->getReplyTo()
        ));
    }
}
