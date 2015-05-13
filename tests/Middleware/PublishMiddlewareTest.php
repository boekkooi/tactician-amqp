<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Middleware\PublishMiddleware;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\Publisher;
use Mockery;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class PublishMiddlewareTest extends \PHPUnit_Framework_TestCase
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
            ->with($message)
            ->andReturn('rpc');

        $this->middleware->execute($message, $this->mockInvalidNext());
    }

    /**
     * @test
     */
    public function it_should_continue_if_its_not_a_command()
    {
        $nextWasCalled = false;

        $command = new \stdClass();
        $this->middleware->execute($command, function ($nextCommand) use ($command, &$nextWasCalled) {
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
