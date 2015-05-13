<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\AMQPCommand;
use Boekkooi\Tactician\AMQP\Middleware\EnvelopeTransformerMiddleware;
use Boekkooi\Tactician\AMQP\Transformer\EnvelopeTransformer;
use Mockery;

class EnvelopeTransformerMiddlewareTest extends \PHPUnit_Framework_TestCase
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
            ->with($envelope)
            ->andReturn($command);

        $nextWasCalled = false;
        $this->middleware->execute($amqpCommand, function ($nextCommand) use ($command, &$nextWasCalled) {
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
    public function it_should_pass_trough_none_amqp_commands()
    {
        $this->transformer->shouldNotReceive('transformEnvelopeToCommand');

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

    public function provide_envelope_command_map()
    {
        $env1 = Mockery::mock(\AMQPEnvelope::class);
        $env2 = Mockery::mock(\AMQPEnvelope::class);
        return [
            [ $env1, $env1, new \stdClass() ],
            [ new AMQPCommand($env2, Mockery::mock(\AMQPQueue::class)), $env2, 'a_command' ]
        ];
    }
}
