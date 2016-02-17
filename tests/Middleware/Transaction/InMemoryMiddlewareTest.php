<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware\Transaction;

use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Middleware\Transaction\InMemoryMiddleware;
use Boekkooi\Tactician\AMQP\Publisher\Locator\PublisherLocator;
use Boekkooi\Tactician\AMQP\Publisher\MessageCapturer;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Tests\Boekkooi\Tactician\AMQP\Middleware\MiddlewareTestCase;
use Mockery;

class InMemoryMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var InMemoryMiddleware
     */
    private $middleware;
    /**
     * @var MessageCapturer|Mockery\MockInterface
     */
    private $capturer;
    /**
     * @var PublisherLocator|Mockery\MockInterface
     */
    private $locator;

    public function setUp()
    {
        $this->capturer = Mockery::mock(MessageCapturer::class);
        $this->locator = Mockery::mock(PublisherLocator::class);
        $this->middleware = new InMemoryMiddleware($this->capturer, $this->locator);
    }

    /**
     * @test
     */
    public function it_publishes_it_messages_that_where_captured()
    {
        $messages = [
            Mockery::mock(Message::class),
            Mockery::mock(Message::class),
            Mockery::mock(Message::class)
        ];

        $this->capturer->shouldNotReceive('clear');
        $this->capturer
            ->shouldReceive('fetchMessages')
            ->withNoArgs()
            ->once()
            ->andReturn($messages);

        $this->mockMessagePublish($messages);

        $this->execute($this->middleware, 'some_command', 'some_command');
    }

    /**
     * @test
     */
    public function it_discards_messages_that_where_captured_when_a_exception_occured()
    {
        $this->capturer
            ->shouldReceive('clear')
            ->withNoArgs()
            ->once();
        $this->capturer->shouldNotReceive('fetchMessages');

        $this->setExpectedException(\RuntimeException::class, 'Failed');

        $this->middleware->execute('some_command', function () {
            throw new \RuntimeException('Failed');
        });
    }

    /**
     * @test
     */
    public function it_publishes_message_once_internal_calls_where_handled()
    {
        $capturer = new MessageCapturer();
        $middleware = new InMemoryMiddleware($capturer, $this->locator);

        $message1 = Mockery::mock(Message::class);
        $message2 = Mockery::mock(Message::class);
        $message3 = Mockery::mock(Message::class);
        $message4 = Mockery::mock(Message::class);
        $message5 = Mockery::mock(Message::class);

        $push1 = function ($m) use ($capturer, $message1) { $capturer->publish($message1); };
        $push2 = function ($m) use ($capturer, $message2) {
            $capturer->publish($message2);
            throw new \RuntimeException('Failed');
        };
        $push3 = function ($m) use ($middleware, $capturer, $message3, $message4, $message5) {
            $capturer->publish($message3);

            try {
                $middleware->execute($m, function () use ($capturer, $message4) {
                    $capturer->publish($message4);
                    throw new \RuntimeException('Failed');
                });
            } catch (\RuntimeException $e) {
            }

            $middleware->execute($m, function () use ($capturer, $message5) {
                $capturer->publish($message5);
            });
        };

        $chain = function ($m) use ($middleware, $push1, $push2, $push3) {
            $middleware->execute($m, $push1);

            try {
                $middleware->execute($m, $push2);
            } catch (\RuntimeException $e) {
            }

            $middleware->execute($m, $push3);
        };

        $this->mockMessagePublish([
            $message1,
            $message3,
            $message5
        ]);

        $middleware->execute('some_command', $chain);
    }

    private function mockMessagePublish(array $messages)
    {
        foreach ($messages as $message) {
            $publisher = Mockery::mock(Publisher::class);
            $publisher
                ->shouldReceive('publish')
                ->with($message)
                ->once()
            ;

            $this->locator
                ->shouldReceive('getPublisherForMessage')
                ->with($message)
                ->andReturn($publisher)
                ->once()
            ;
        }
    }
}
