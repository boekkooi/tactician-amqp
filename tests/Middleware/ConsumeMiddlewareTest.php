<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Command;
use Boekkooi\Tactician\AMQP\Middleware\ConsumeMiddleware;
use Mockery;

class ConsumeMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var \AMQPQueue|Mockery\MockInterface
     */
    private $queue;

    /**
     * @var \AMQPEnvelope|Mockery\MockInterface
     */
    private $envelope;

    public function setUp()
    {
        $this->queue = Mockery::mock(\AMQPQueue::class);
        $this->envelope = Mockery::mock(\AMQPEnvelope::class);
        $this->command = new Command($this->envelope, $this->queue);
    }

    /**
     * @test
     */
    public function it_should_ack_a_envelope_when_there_is_no_exception()
    {
        $this->queue
            ->shouldReceive('ack')
            ->once()
            ->with('tag');

        $this->envelope
            ->shouldReceive('getDeliveryTag')
            ->withNoArgs()
            ->andReturn('tag');

        $command = $this->command;
        $middleware = new ConsumeMiddleware();

        $this->execute($middleware, $command, $command);
    }

    /**
     * @test
     */
    public function it_should_requeue_a_envelope_when_there_is_a_exception()
    {
        $middleware = new ConsumeMiddleware(true);

        $this->queue
            ->shouldNotReceive('ack')
            ->with('tag');

        $this->queue
            ->shouldReceive('reject')
            ->once()
            ->with('tag', AMQP_REQUEUE);

        $this->envelope
            ->shouldReceive('getDeliveryTag')
            ->withNoArgs()
            ->andReturn('tag');

        $this->setExpectedException(\RuntimeException::class, 'The queue should requeue the message now');
        $middleware->execute($this->command, function () {
            throw new \RuntimeException('The queue should requeue the message now');
        });
    }

    /**
     * @test
     */
    public function it_should_reject_a_envelope_when_there_is_a_exception()
    {
        $middleware = new ConsumeMiddleware(false);

        $this->queue
            ->shouldNotReceive('ack')
            ->with('tag');

        $this->queue
            ->shouldReceive('reject')
            ->once()
            ->with('tag', AMQP_NOPARAM);

        $this->envelope
            ->shouldReceive('getDeliveryTag')
            ->withNoArgs()
            ->andReturn('tag');

        $this->setExpectedException(\RuntimeException::class, 'The queue should reject the message now');
        $middleware->execute($this->command, function () {
            throw new \RuntimeException('The queue should reject the message now');
        });
    }

    /**
     * @test
     */
    public function it_should_pass_trough_none_amqp_command()
    {
        $command = new \stdClass();
        $middleware = new ConsumeMiddleware();

        $this->execute($middleware, $command, $command);
    }
}
