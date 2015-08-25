<?php
namespace Tests\Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;
use Boekkooi\Tactician\AMQP\Publisher\ExchangeDirectPublisher;
use Tests\Boekkooi\Tactician\AMQP\Fixtures\Command\MessageCommand;
use Mockery;

class ExchangeDirectPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AMQPExchange|Mockery\MockInterface
     */
    private $exchange;

    /**
     * @var ExchangeDirectPublisher
     */
    private $publisher;

    /**
     * @test
     */
    public function setUp()
    {
        $this->exchange = Mockery::mock(\AMQPExchange::class);
        $this->publisher = new ExchangeDirectPublisher($this->exchange);
    }

    /**
     * @test
     */
    public function it_should_publish_a_message()
    {
        $message = new MessageCommand('message', 'key');

        $this->exchange
            ->shouldReceive('publish')
            ->once()
            ->with(
                $message->getMessage(),
                $message->getRoutingKey(),
                $message->getFlags(),
                $message->getAttributes()
            )
            ->andReturn(true);

        $this->publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_publish_fails()
    {
        $message = new MessageCommand('message');

        $this->exchange
            ->shouldReceive('publish')
            ->once()
            ->with(
                $message->getMessage(),
                $message->getRoutingKey(),
                $message->getFlags(),
                $message->getAttributes()
            )
            ->andReturn(false);

        $this->setExpectedException(FailedToPublishException::class);
        $this->publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_wrap_amqp_exception()
    {
        $message = new MessageCommand('message', 'key');

        $this->exchange
            ->shouldReceive('publish')
            ->once()
            ->andThrow(\AMQPExchangeException::class);

        try {
            $this->publisher->publish($message);

            $this->fail('A exception should have been throw by exchange::publish');
        } catch (FailedToPublishException $e) {
            $this->assertInstanceOf(
                \AMQPExchangeException::class,
                $e->getPrevious(),
                'Expected the previous exception to be the throw to be a \AMQPExchangeException'
            );
            $this->assertEquals($message, $e->getTacticianMessage());
            return;
        } catch (\Exception $e) {
            $this->fail(sprintf(
                'A exception of type %s was expected but got %s',
                \AMQPExchangeException::class,
                get_class($e)
            ));
        }
    }
}
