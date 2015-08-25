<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Command;
use Boekkooi\Tactician\AMQP\Middleware\EnvelopeTransformerMiddleware;
use Boekkooi\Tactician\AMQP\Transformer\EnvelopeTransformer;
use Mockery;

class EnvelopeTransformerMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var EnvelopeTransformer|Mockery\MockInterface
     */
    private $transformer;

    /**
     * @var EnvelopeTransformerMiddleware
     */
    private $middleware;

    public function setUp()
    {
        $this->transformer = Mockery::mock(EnvelopeTransformer::class);
        $this->middleware = new EnvelopeTransformerMiddleware($this->transformer);
    }

    /**
     * @test
     * @dataProvider provide_envelope_command_map
     */
    public function it_should_transform_envelope_commands($amqpCommand, $envelope, $command)
    {
        $this->transformer
            ->shouldReceive('transformEnvelopeToCommand')
            ->atLeast()->once()
            ->with($envelope)
            ->andReturn($command);

        $this->execute($this->middleware, $amqpCommand, $command);
    }

    /**
     * @test
     */
    public function it_should_pass_trough_none_amqp_commands()
    {
        $this->transformer->shouldNotReceive('transformEnvelopeToCommand');

        $command = new \stdClass();
        $this->execute($this->middleware, $command, $command);
    }

    public function provide_envelope_command_map()
    {
        $env1 = Mockery::mock(\AMQPEnvelope::class);
        $env2 = Mockery::mock(\AMQPEnvelope::class);
        return [
            [ $env1, $env1, new \stdClass() ],
            [ new Command($env2, Mockery::mock(\AMQPQueue::class)), $env2, 'a_command' ]
        ];
    }
}
