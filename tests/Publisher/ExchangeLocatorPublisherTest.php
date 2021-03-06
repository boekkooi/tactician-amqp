<?php
namespace Tests\Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Exception\MissingExchangeException;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;
use Boekkooi\Tactician\AMQP\ExchangeLocator\ExchangeLocator;
use Boekkooi\Tactician\AMQP\Publisher\ExchangeLocatorPublisher;
use Tests\Boekkooi\Tactician\AMQP\Fixtures\Command\MessageCommand;
use Mockery;

class ExchangeLocatorPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExchangeLocator|Mockery\MockInterface
     */
    private $locator;

    /**
     * @var ExchangeLocatorPublisher
     */
    private $publisher;

    public function setUp()
    {
        $this->locator = Mockery::mock(ExchangeLocator::class);
        $this->publisher = new ExchangeLocatorPublisher($this->locator);
    }

    /**
     * @test
     */
    public function it_should_publish_a_message()
    {
        $message = new MessageCommand('message', 'key');

        $exchange = $this->mockExchangeWithPublish($message);

        $this->locator
            ->shouldReceive('getExchangeForMessage')
            ->atLeast()->once()
            ->with($message)
            ->andReturn($exchange);

        $this->publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_locater_returns_no_exchange()
    {
        $message = new MessageCommand('message');

        $this->locator
            ->shouldReceive('getExchangeForMessage')
            ->atLeast()->once()
            ->with($message)
            ->andReturn(null);

        $this->setExpectedException(MissingExchangeException::class);
        $this->publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_publish_fails()
    {
        $message = new MessageCommand('message');
        $exchange = $this->mockExchangeWithPublish($message, false);

        $this->locator
            ->shouldReceive('getExchangeForMessage')
            ->atLeast()->once()
            ->with($message)
            ->andReturn($exchange);

        $this->setExpectedException(FailedToPublishException::class);
        $this->publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_wrap_amqp_exception()
    {
        $message = new MessageCommand('message', 'key');

        $exchange = Mockery::mock(\AMQPExchange::class);
        $exchange->shouldReceive('publish')
            ->once()
            ->with(
                $message->getMessage(),
                $message->getRoutingKey(),
                $message->getFlags(),
                $message->getAttributes()
            )
            ->andThrow(\AMQPConnectionException::class);

        $this->locator
            ->shouldReceive('getExchangeForMessage')
            ->atLeast()->once()
            ->with($message)
            ->andReturn($exchange);

        try {
            $this->publisher->publish($message);

            $this->fail('A exception should have been throw by exchange::publish');
        } catch (FailedToPublishException $e) {
            $this->assertInstanceOf(
                \AMQPConnectionException::class,
                $e->getPrevious(),
                'Expected the previous exception to be the throw to be a \AMQPConnectionException'
            );
            return;
        } catch (\Exception $e) {
            $this->fail(sprintf(
                'A exception of type %s was expected but got %s',
                \AMQPConnectionException::class,
                get_class($e)
            ));
        }
    }

    /**
     * @param Message $message
     * @param bool $return
     * @return Mockery\MockInterface
     */
    private function mockExchangeWithPublish(Message $message, $return = true)
    {
        $exchange = Mockery::mock(\AMQPExchange::class);
        $exchange
            ->shouldReceive('publish')
            ->once()
            ->with(
                $message->getMessage(),
                $message->getRoutingKey(),
                $message->getFlags(),
                $message->getAttributes()
            )
            ->andReturn($return);

        return $exchange;
    }
}
