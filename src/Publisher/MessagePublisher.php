<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\ExchangeLocator\ExchangeLocator;
use Boekkooi\Tactician\AMQP\Message;

/**
 * A message publisher that will use a exchange locator to publish a message
 */
class MessagePublisher extends ExchangePublisher
{
    /**
     * @var ExchangeLocator
     */
    private $exchangeLocator;

    /**
     * @param ExchangeLocator $exchangeLocator
     */
    public function __construct(ExchangeLocator $exchangeLocator)
    {
        $this->exchangeLocator = $exchangeLocator;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExchange(Message $message)
    {
        return $this->exchangeLocator->getExchangeForMessage($message);
    }
}
