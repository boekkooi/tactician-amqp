<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Middleware\PublishMiddleware;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Locator\PublisherLocator;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Mockery;

class PublishMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var PublishMiddleware
     */
    private $middleware;

    /**
     * @var PublisherLocator|Mockery\MockInterface
     */
    private $locator;

    public function setUp()
    {
        $this->locator = Mockery::mock(PublisherLocator::class);
        $this->middleware = new PublishMiddleware($this->locator);
    }

    /**
     * @test
     */
    public function it_should_publish_a_message()
    {
        $message = Mockery::mock(Message::class);

        $publisher = Mockery::mock(Publisher::class);
        $publisher
            ->shouldReceive('publish')
            ->once()
            ->with($message)
            ->andReturn('rpc');

        $this->locator
            ->shouldReceive('getPublisherForMessage')
            ->atLeast()->once()
            ->with($message)
            ->andReturn($publisher);

        $this->assertEquals('rpc',
            $this->middleware->execute(
                $message,
                function () {
                    throw new \LogicException('Middleware fell through to next callable, this should not happen in the test.');
                }
            )
        );
    }

    /**
     * @test
     */
    public function it_should_continue_if_its_not_a_message()
    {
        $this->locator->shouldNotReceive('getPublisherForMessage');

        $command = new \stdClass();
        $this->execute($this->middleware, $command, $command);
    }
}
