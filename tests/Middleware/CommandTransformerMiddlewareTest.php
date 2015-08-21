<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Exception\CommandTransformationException;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Middleware\CommandTransformerMiddleware;
use Boekkooi\Tactician\AMQP\Transformer\CommandTransformer;
use League\Tactician\Middleware;
use Mockery;

class CommandTransformerMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var CommandTransformer|Mockery\MockInterface
     */
    private $transformer;
    /**
     * @var CommandTransformerMiddleware
     */
    private $middleware;

    public function setUp()
    {
        $this->transformer = Mockery::mock(CommandTransformer::class);
        $this->middleware = new CommandTransformerMiddleware($this->transformer);
    }

    /**
     * @test
     */
    public function it_should_transform_a_registered_command()
    {
        $command = new \stdClass();
        $message = Mockery::mock(Message::class);

        $this->middleware->addSupportedCommand('\stdClass');

        $this->transformer
            ->shouldReceive('transformCommandToMessage')
            ->atLeast()->once()
            ->with($command)
            ->andReturn($message);

        $this->execute($this->middleware, $command, $message);
    }

    /**
     * @test
     */
    public function it_should_pass_trough_unknown_commands()
    {
        $this->transformer->shouldNotReceive('transformCommandToMessage');

        $command = new \stdClass();
        $this->execute($this->middleware, $command, $command);
    }

    /**
     * @test
     */
    public function it_should_fail_when_no_message_is_returned()
    {
        $command = new \stdClass();

        $this->middleware->addSupportedCommand('\stdClass');

        $this->transformer
            ->shouldReceive('transformCommandToMessage')
            ->atLeast()->once()
            ->with($command)
            ->andReturn(null);

        $this->setExpectedException(CommandTransformationException::class);
        $this->middleware->execute(
            $command,
            function () {
                throw new \LogicException('Next should not have been called!');
            }
        );
    }

    /**
     * @test
     */
    public function it_should_register_multiple_commands()
    {
        $this->middleware->addSupportedCommand('\stdClass');
        $this->middleware->addSupportedCommands(['Yet\Another\Command', 'Another\Command']);

        $this->assertEquals(
            ['stdClass', 'Yet\Another\Command', 'Another\Command'],
            $this->middleware->getSupportedCommands()
        );
    }

    /**
     * @return Middleware
     */
    public function getMiddleware()
    {
        $this->middleware;
    }
}
