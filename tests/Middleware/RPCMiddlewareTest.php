<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\AMQPCommand;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Middleware\RPCMiddleware;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Boekkooi\Tactician\AMQP\Transformer\ResponseTransformer;
use Mockery;

class RPCMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RPCMiddlewarePatched
     */
    private $middleware;

    /**
     * @var Publisher|Mockery\MockInterface
     */
    private $publisher;
    /**
     * @var ResponseTransformer|Mockery\MockInterface
     */
    private $transformer;

    public function setUp()
    {
        $this->publisher = Mockery::mock(Publisher::class);
        $this->transformer = Mockery::mock(ResponseTransformer::class);

        $this->middleware = new RPCMiddlewarePatched($this->transformer);
        $this->middleware->patchPublisher($this->publisher);
    }

    /**
     * @test
     */
    public function it_should_publish_a_rpc_command_result()
    {
        $commandResult = [ 'YAY' ];
        $resultMessage = Mockery::mock(Message::class);

        $envelope = Mockery::mock(\AMQPEnvelope::class);
        $envelope
            ->shouldReceive('getReplyTo')
            ->atLeast()->once()
            ->andReturn('my-reply-id');
        $command = Mockery::mock(AMQPCommand::class);
        $command
            ->shouldReceive('getEnvelope')
            ->atLeast()->once()
            ->andReturn($envelope);

        $this->transformer
            ->shouldReceive('transformCommandResponse')
            ->atLeast()->once()
            ->with($commandResult)
            ->andReturn($resultMessage);
        $this->publisher
            ->shouldReceive('publish')
            ->once()
            ->with($resultMessage);

        $nextWasCalled = false;
        $this->middleware->execute($command, function ($nextCommand) use ($command, &$nextWasCalled, $commandResult) {
            \PHPUnit_Framework_Assert::assertSame($command, $nextCommand);
            $nextWasCalled = true;

            return $commandResult;
        });

        if (!$nextWasCalled) {
            throw new \LogicException('Middleware should have called the next callable.');
        }
    }

    /**
     * @test
     */
    public function it_should_pass_trough_none_amqp_commands()
    {
        $this->transformer->shouldNotReceive('transformCommandResponse');
        $this->publisher->shouldNotReceive('publish');

        $command = new \stdClass();
        $nextWasCalled = false;
        $this->middleware->execute($command, function ($nextCommand) use ($command, &$nextWasCalled) {
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
    public function it_should_pass_trough_none_rpc_commands()
    {
        $this->transformer->shouldNotReceive('transformCommandResponse');
        $this->publisher->shouldNotReceive('publish');

        $envelope = Mockery::mock(\AMQPEnvelope::class);
        $envelope
            ->shouldReceive('getReplyTo')
            ->atLeast()->once()
            ->andReturn('');

        $command = Mockery::mock(AMQPCommand::class);
        $command
            ->shouldReceive('getEnvelope')
            ->atLeast()->once()
            ->andReturn($envelope);

        $nextWasCalled = false;
        $this->middleware->execute($command, function ($nextCommand) use ($command, &$nextWasCalled) {
            \PHPUnit_Framework_Assert::assertSame($command, $nextCommand);
            $nextWasCalled = true;
        });

        if (!$nextWasCalled) {
            throw new \LogicException('Middleware should have called the next callable.');
        }
    }
}

class RPCMiddlewarePatched extends RPCMiddleware
{
    private $publisher;

    public function patchPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    protected function getPublisher(AMQPCommand $command)
    {
        return $this->publisher;
    }
}
