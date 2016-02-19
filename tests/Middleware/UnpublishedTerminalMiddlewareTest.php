<?php declare (strict_types = 1);
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use Boekkooi\Tactician\AMQP\Exception\CommandNotPublishedException;
use Boekkooi\Tactician\AMQP\Middleware\UnpublishedTerminalMiddleware;

class UnpublishedTerminalMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideAnyTypeOfCommand
     *
     * @param mixed $command
     */
    public function it_should_always_throw_a_exception($command)
    {
        $middleware = new UnpublishedTerminalMiddleware();

        $this->setExpectedException(CommandNotPublishedException::class);
        $middleware->execute($command, function () {
            throw new \LogicException('Middleware fell through to next callable, this should not have happend.');
        });
    }

    public function provideAnyTypeOfCommand()
    {
        return [
            [ 1 ],
            [ new \stdClass() ],
            [ null ],
            [ 'a string' ],
            [ new \SplFileInfo(__FILE__) ],
            [ true ],
            [ false ],
            [ [] ],
            [ [ [ 1 ] ] ],
        ];
    }
}
