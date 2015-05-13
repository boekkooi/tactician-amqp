<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\AMQPCommand;
use Boekkooi\Tactician\AMQP\Middleware\ConsumeMiddleware;
use Mockery;

class ConsumeMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPCommand
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
        $this->command = new AMQPCommand($this->envelope, $this->queue);
    }

    /**
     * @test
     */
    public function it_should_ack_a_envelope_when_there_is_no_exception()
    {
        $middleware = new ConsumeMiddleware();

        $this->queue
            ->shouldReceive('ack')
            ->with('tag');

        $this->envelope
            ->shouldReceive('getDeliveryTag')
            ->withNoArgs()
            ->andReturn('tag');

        $command = $this->command;
        $nextWasCalled = false;

        $middleware->execute($command, function($nextCommand) use ($command, &$nextWasCalled) {
            \PHPUnit_Framework_Assert::assertSame($command, $nextCommand);
            $nextWasCalled = true;
        });

        if (!$nextWasCalled) {
            throw new \LogicException('Middleware should have called the next callable.');
        }
    }

    /**
     * @test
     */
    public function it_should_requeue_a_envelope_when_there_is_a_exception()
    {
        $this->setExpectedException(\RuntimeException::class, 'The queue should requeue the message now');

        $middleware = new ConsumeMiddleware(true);

        $this->queue
            ->shouldNotReceive('ack')
            ->with('tag');

        $this->queue
            ->shouldReceive('reject')
            ->with('tag', AMQP_REQUEUE);

        $this->envelope
            ->shouldReceive('getDeliveryTag')
            ->withNoArgs()
            ->andReturn('tag');

        $middleware->execute($this->command, function() {
            throw new \RuntimeException('The queue should requeue the message now');
        });
    }

    /**
     * @test
     */
    public function it_should_reject_a_envelope_when_there_is_a_exception()
    {
        $this->setExpectedException(\RuntimeException::class, 'The queue should reject the message now');

        $middleware = new ConsumeMiddleware(false);

        $this->queue
            ->shouldNotReceive('ack')
            ->with('tag');

        $this->queue
            ->shouldReceive('reject')
            ->with('tag', AMQP_NOPARAM);

        $this->envelope
            ->shouldReceive('getDeliveryTag')
            ->withNoArgs()
            ->andReturn('tag');

        $middleware->execute($this->command, function() {
            throw new \RuntimeException('The queue should reject the message now');
        });
    }

    /**
     * @test
     */
    public function it_should_pass_trough_none_amqp_command()
    {
        $middleware = new ConsumeMiddleware();

        $command = new \stdClass();
        $nextWasCalled = false;
        $middleware->execute($command, function ($nextCommand) use ($command, &$nextWasCalled) {
            \PHPUnit_Framework_Assert::assertSame($command, $nextCommand);
            $nextWasCalled = true;
        });

        if (!$nextWasCalled) {
            throw new \LogicException('Middleware should have called the next callable.');
        }
    }

    /**
     * @return callable
     */
    protected function mockInvalidNext()
    {
        return function () {
            throw new \LogicException('Middleware fell through to next callable, this should not happen in the test.');
        };
    }
}
