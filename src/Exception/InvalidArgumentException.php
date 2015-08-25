<?php
namespace Boekkooi\Tactician\AMQP\Exception;

use Boekkooi\Tactician\AMQP\Command;

class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
    public static function forMissingCommandReplyTo(Command $command)
    {
        return new static(sprintf(
            'Expect command envelope to have a reply-to but "%s" was found',
            $command->getEnvelope()->getReplyTo()
        ));
    }
}
