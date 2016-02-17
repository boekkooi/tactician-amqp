<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Middleware\MessageCapturingMiddleware;
use Boekkooi\Tactician\AMQP\Publisher\MessageCapturer;
use Mockery;

class MessageCapturingMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var MessageCapturingMiddleware
     */
    private $middleware;

    /**
     * @var MessageCapturer|Mockery\MockInterface
     */
    private $capturer;

    public function setUp()
    {
        $this->capturer = Mockery::mock(MessageCapturer::class);
        $this->middleware = new MessageCapturingMiddleware($this->capturer);
    }

    /**
     * @test
     */
    public function it_should_capture_a_message()
    {
        $message = Mockery::mock(Message::class);

        $this->capturer
            ->shouldReceive('publish')
            ->once()
            ->with($message);

        $this->assertNull(
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
        $this->capturer->shouldReceive('publish');

        $command = new \stdClass();
        $this->execute($this->middleware, $command, $command);
    }
}
