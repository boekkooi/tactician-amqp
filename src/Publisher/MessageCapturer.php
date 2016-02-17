<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Message;

/**
 * A publisher that simply stores the publish messages.
 */
class MessageCapturer implements Publisher
{
    /**
     * @var Message[]
     */
    private $messages = [];

    /**
     * Store a message for later use.
     *
     * @param Message $message
     * @return void
     */
    public function publish(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Fetch the captured messages.
     *
     * @param bool $clear Clear the stored messages
     * @return Message[]
     */
    public function fetchMessages($clear = true)
    {
        $messages = $this->messages;
        if ($clear) {
            $this->clear();
        }

        return $messages;
    }

    /**
     * Clear all captured messages.
     */
    public function clear()
    {
        $this->messages = [];
    }
}
