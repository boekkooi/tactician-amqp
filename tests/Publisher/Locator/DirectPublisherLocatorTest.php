<?php
namespace Tests\Boekkooi\Tactician\AMQP\Publisher\Locator;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Locator\DirectPublisherLocator;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Mockery;

class DirectPublisherLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_return_the_set_publisher()
    {
        /** @var Message|Mockery\MockInterface $message */
        $message = Mockery::mock(Message::class);

        /** @var Publisher|Mockery\MockInterface $publisher */
        $publisher = Mockery::mock(Publisher::class);

        $locator = new DirectPublisherLocator($publisher);
        $this->assertSame(
            $publisher,
            $locator->getPublisherForMessage($message)
        );
    }
}
