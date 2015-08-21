<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Middleware\PublishMiddleware;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Mockery;

class PublishMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var PublishMiddleware
     */
    private $middleware;

    /**
     * @var Publisher|Mockery\MockInterface
     */
    private $publisher;

    public function setUp()
    {
        $this->publisher = Mockery::mock(Publisher::class);
        $this->middleware = new PublishMiddleware($this->publisher);
    }

    /**
     * @test
     */
    public function it_should_publish_a_message()
    {
        $message = Mockery::mock(Message::class);

        $this->publisher
            ->shouldReceive('publish')
            ->once()
            ->with($message)
            ->andReturn('rpc');

        $this->middleware->execute(
            $message,
            function () {
                throw new \LogicException('Middleware fell through to next callable, this should not happen in the test.');
            }
        );
    }

    /**
     * @test
     */
    public function it_should_continue_if_its_not_a_command()
    {
        $command = new \stdClass();
        $this->execute($this->middleware, $command, $command);
    }
}
