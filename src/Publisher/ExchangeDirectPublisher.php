<?php
namespace Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Message;

/**
 * A publisher that uses a single exchange
 */
class ExchangeDirectPublisher extends ExchangePublisher
{
    /**
     * @var \AMQPExchange
     */
    private $exchange;

    /**
     * @param \AMQPExchange $exchange
     */
    public function __construct(\AMQPExchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExchange(Message $message)
    {
        return $this->exchange;
    }
}
