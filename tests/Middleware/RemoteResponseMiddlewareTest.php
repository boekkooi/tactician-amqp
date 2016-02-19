<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Command;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Middleware\RemoteResponseMiddleware;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Boekkooi\Tactician\AMQP\Publisher\RemoteProcedure\ResponsePublisher;
use Boekkooi\Tactician\AMQP\Transformer\ResponseTransformer;
use Mockery;

class RemoteResponseMiddlewareTest extends MiddlewareTestCase
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
        $commandResult = ['YAY'];
        $resultMessage = Mockery::mock(Message::class);

        $envelope = Mockery::mock(\AMQPEnvelope::class);
        $envelope
            ->shouldReceive('getReplyTo')
            ->atLeast()->once()
            ->andReturn('my-reply-id');
        $command = Mockery::mock(Command::class);
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

        $this->execute($this->middleware, $command, $command, $commandResult);
    }

    /**
     * @test
     */
    public function it_uses_the_remote_procedure_response_publisher()
    {
        $command = Mockery::mock(Command::class);
        $command
            ->shouldReceive('getQueue->getChannel')
            ->andReturn(Mockery::mock(\AMQPChannel::class));
        $command
            ->shouldReceive('getEnvelope->getReplyTo')
            ->andReturn('reply-to');
        $command
            ->shouldReceive('getEnvelope->getCorrelationId')
            ->andReturn(null);

        $this->assertInstanceOf(
            ResponsePublisher::class,
            $this->middleware->getNativePublisher($command)
        );
    }

    /**
     * @test
     */
    public function it_should_pass_trough_none_amqp_commands()
    {
        $this->transformer->shouldNotReceive('transformCommandResponse');
        $this->publisher->shouldNotReceive('publish');

        $command = new \stdClass();
        $this->execute($this->middleware, $command, $command);
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

        $command = Mockery::mock(Command::class);
        $command
            ->shouldReceive('getEnvelope')
            ->atLeast()->once()
            ->andReturn($envelope);

        $this->execute($this->middleware, $command, $command);
    }
}

class RPCMiddlewarePatched extends RemoteResponseMiddleware
{
    private $publisher;

    public function patchPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    protected function getPublisher(Command $command)
    {
        return $this->publisher;
    }

    public function getNativePublisher(Command $command)
    {
        return parent::getPublisher($command);
    }
}
