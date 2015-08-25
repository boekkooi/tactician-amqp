<?php
namespace Boekkooi\Tactician\AMQP\Publisher\Locator;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;

class DirectPublisherLocator implements PublisherLocator
{
    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @inheritdoc
     */
    public function getPublisherForMessage(Message $message)
    {
        return $this->publisher;
    }
}
