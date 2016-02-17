<?php
namespace Boekkooi\Tactician\AMQP\Middleware\Transaction;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\MessageCapturer;
use Boekkooi\Tactician\AMQP\Publisher\Locator\PublisherLocator;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use League\Tactician\Middleware;

class InMemoryMiddleware implements Middleware
{
    /**
     * @var MessageCapturer
     */
    private $capturer;

    /**
     * @var PublisherLocator
     */
    private $locator;

    /**
     * @var int
     */
    private $transactionLevel = 0;

    /**
     * @var Message[]
     */
    private $transactionMessages = [];

    /**
     * @param MessageCapturer|Publisher $capturer
     * @param PublisherLocator $locator
     */
    public function __construct(MessageCapturer $capturer, PublisherLocator $locator)
    {
        $this->capturer = $capturer;
        $this->locator = $locator;
    }

    /**
     * Executes the given command and optionally returns a value
     *
     * @param object $command
     * @param callable $next
     * @return mixed
     * @throws \Error
     * @throws \Exception
     */
    public function execute($command, callable $next)
    {
        if ($this->transactionLevel > 0) {
            $this->storeMessages();
        }

        ++$this->transactionLevel;

        end($this->transactionMessages);
        $savepoint = key($this->transactionMessages);
        $savepoint = $savepoint === null ? 0 : ++$savepoint;

        try {
            $returnValue = $next($command);

            $this->storeMessages();

            --$this->transactionLevel;
        } catch (\Exception $e) {
            $this->capturer->clear();
            array_splice($this->transactionMessages, $savepoint);

            --$this->transactionLevel;
            throw $e;
        } catch (\Error $e) {
            $this->capturer->clear();
            array_splice($this->transactionMessages, $savepoint);

            --$this->transactionLevel;
            throw $e;
        }

        if ($this->transactionLevel === 0) {
            $this->publish();
        }

        return $returnValue;
    }

    protected function publish()
    {
        foreach ($this->transactionMessages as $message) {
            $this->locator
                ->getPublisherForMessage($message)
                ->publish($message);
        }

        $this->transactionMessages = [];
    }

    /**
     * @param $messages
     */
    private function storeMessages()
    {
        $messages = $this->capturer->fetchMessages();
        foreach ($messages as $message) {
            $this->transactionMessages[] = $message;
        }
    }
}
