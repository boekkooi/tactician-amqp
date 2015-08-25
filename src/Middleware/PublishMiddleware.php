<?php
namespace Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Locator\PublisherLocator;
use League\Tactician\Middleware;

/**
 * A middleware that will publish AMQP message to a publisher
 */
class PublishMiddleware implements Middleware
{
    /**
     * @var PublisherLocator
     */
    private $locator;

    /**
     * @param PublisherLocator $locator
     */
    public function __construct(PublisherLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if (!$command instanceof Message) {
            return $next($command);
        }

        return $this->locator
            ->getPublisherForMessage($command)
            ->publish($command);
    }
}
