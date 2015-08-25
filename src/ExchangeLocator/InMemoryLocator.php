<?php
namespace Boekkooi\Tactician\AMQP\ExchangeLocator;

use Boekkooi\Tactician\AMQP\Exception\MissingExchangeException;
use Boekkooi\Tactician\AMQP\Message;

class InMemoryLocator implements ExchangeLocator
{
    /**
     * @var \AMQPExchange[]
     */
    protected $exchanges = [];

    /**
     * @param array $commandClassToHandlerMap
     */
    public function __construct(array $commandClassToHandlerMap = [])
    {
        $this->addExchanges($commandClassToHandlerMap);
    }

    /**
     * Bind a exchange instance to receive all commands with a certain class
     *
     * @param \AMQPExchange $exchange Exchange to receive the command
     * @param string $messageClassName Command class e.g. "My\TaskAddedCommand"
     */
    public function addExchange(\AMQPExchange $exchange, $messageClassName)
    {
        $this->exchanges[$messageClassName] = $exchange;
    }

    /**
     * Allows you to add multiple exchanges at once.
     *
     * The map should be an array in the format of:
     *  [
     *      AddTaskCommand::class      => $someAMQPExchangeInstance,
     *      CompleteTaskCommand::class => $someAMQPExchangeInstance,
     *  ]
     *
     * @param array $commandClassToHandlerMap
     */
    protected function addExchanges(array $commandClassToHandlerMap)
    {
        foreach ($commandClassToHandlerMap as $messageClass => $handler) {
            $this->addExchange($handler, $messageClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeForMessage(Message $message)
    {
        $class = get_class($message);

        if (!isset($this->exchanges[$class])) {
            throw MissingExchangeException::forMessage($message);
        }

        return $this->exchanges[$class];
    }
}
