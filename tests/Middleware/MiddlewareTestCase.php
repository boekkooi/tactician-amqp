<?php
namespace Tests\Boekkooi\Tactician\AMQP\Middleware;

use League\Tactician\Middleware;

abstract class MiddlewareTestCase extends \PHPUnit_Framework_TestCase
{
    protected function execute(Middleware $middleware, $command, $expectedNextCommand, $executeResult = null)
    {
        $nextWasCalled = false;
        $middleware->execute(
            $command,
            function ($nextCommand) use ($expectedNextCommand, &$nextWasCalled, $executeResult) {
                \PHPUnit_Framework_Assert::assertSame($expectedNextCommand, $nextCommand);
                $nextWasCalled = true;

                return $executeResult;
            }
        );

        if (!$nextWasCalled) {
            throw new \LogicException('Middleware should have called the next callable.');
        }
    }
}
