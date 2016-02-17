<?php
namespace Tests\Boekkooi\Tactician\AMQP\Publisher\RemoteProcedure;

use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;
use Boekkooi\Tactician\AMQP\Exception\NoResponseException;
use Boekkooi\Tactician\AMQP\ExchangeLocator\ExchangeLocator;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\RemoteProcedure\CommandPublisher;
use Mockery;

class CommandPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_send_and_receive()
    {
        // Prep
        $queueName = 'response_queue';
        $correlationId = 'cor_id';

        $messageBody = 'message';
        $messageRoutingKey = 'key';
        $messageFlags = AMQP_NOPARAM;
        $messageAttributes = ['correlation_id' => $correlationId];

        $message = $this->mockMessage($messageBody, $messageRoutingKey, $messageFlags, $messageAttributes);
        $exchange = $this->mockExchangeWithPublish($queueName, $messageBody, $messageRoutingKey, $messageFlags, $messageAttributes, true);
        $exchangeLocator = $this->mockExchangeLocator($message, $exchange);

        $envelope = $this->mockResponseEnvelope($correlationId);

        $queue = $this->mockQueue($queueName);
        $queue
            ->shouldReceive('get')
            ->twice()
            ->with(AMQP_AUTOACK)
            ->andReturnValues([
                false,
                $envelope
            ]);

        // Patch
        $publisher = new RemoteProcedureCommandPublisherPatched($exchangeLocator);
        $publisher->patchQueue($queue);

        // Test
        $this->assertSame(
            $envelope,
            $publisher->publish($message)
        );
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_it_fails_to_publish()
    {
        // Prep
        $queueName = 'response_queue';
        $correlationId = 'cor_id';

        $messageBody = 'message';
        $messageRoutingKey = 'key';
        $messageFlags = AMQP_NOPARAM;
        $messageAttributes = ['correlation_id' => $correlationId];

        $message = $this->mockMessage($messageBody, $messageRoutingKey, $messageFlags, $messageAttributes);
        $exchange = $this->mockExchangeWithPublish($queueName, $messageBody, $messageRoutingKey, $messageFlags, $messageAttributes, false);
        $exchangeLocator = $this->mockExchangeLocator($message, $exchange);

        $queue = $this->mockQueue($queueName);
        $queue->shouldNotReceive('get');

        // Patch
        $publisher = new RemoteProcedureCommandPublisherPatched($exchangeLocator);
        $publisher->patchQueue($queue);

        // Test
        $this->setExpectedException(FailedToPublishException::class);
        $publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_it_fails_to_receive_a_message()
    {
        // Prep
        $queueName = 'response_queue';
        $correlationId = 'cor_id';

        $messageBody = 'message';
        $messageRoutingKey = 'key';
        $messageFlags = AMQP_NOPARAM;
        $messageAttributes = ['correlation_id' => $correlationId];

        $message = $this->mockMessage($messageBody, $messageRoutingKey, $messageFlags, $messageAttributes);
        $exchange = $this->mockExchangeWithPublish($queueName, $messageBody, $messageRoutingKey, $messageFlags, $messageAttributes, true);
        $exchangeLocator = $this->mockExchangeLocator($message, $exchange);

        $queue = $this->mockQueue($queueName);
        $queue
            ->shouldReceive('get')
            ->with(AMQP_AUTOACK)
            ->between(50, 100)
            ->andReturn(false);

        // Patch
        $publisher = new RemoteProcedureCommandPublisherPatched($exchangeLocator, 500, null, 5);
        $publisher->patchQueue($queue);

        // Test
        $this->setExpectedException(NoResponseException::class);
        $publisher->publish($message);
    }

    /**
     * @test
     */
    public function it_should_ignore_a_receive_message_with_the_wrong_correlation_id()
    {
        // Prep
        $queueName = 'response_queue';
        $correlationId = 'cor_id';

        $messageBody = 'message';
        $messageRoutingKey = 'key';
        $messageFlags = AMQP_NOPARAM;
        $messageAttributes = ['correlation_id' => $correlationId];

        $message = $this->mockMessage($messageBody, $messageRoutingKey, $messageFlags, $messageAttributes);
        $exchange = $this->mockExchangeWithPublish($queueName, $messageBody, $messageRoutingKey, $messageFlags, $messageAttributes, true);
        $exchangeLocator = $this->mockExchangeLocator($message, $exchange);

        $badEnvelope = $this->mockResponseEnvelope('my_evil_twin_sister');
        $envelope = $this->mockResponseEnvelope($correlationId);

        $queue = $this->mockQueue($queueName);
        $queue
            ->shouldReceive('get')
            ->with(AMQP_AUTOACK)
            ->twice()
            ->andReturnValues([$badEnvelope, $envelope]);

        // Patch
        $publisher = new RemoteProcedureCommandPublisherPatched($exchangeLocator);
        $publisher->patchQueue($queue);

        // Test
        $this->assertSame(
            $envelope,
            $publisher->publish($message)
        );
    }

    /**
     * @param $queueName
     * @return \AMQPQueue|Mockery\MockInterface
     */
    protected function mockQueue($queueName)
    {
        $queue = Mockery::mock(\AMQPQueue::class);
        $queue
            ->shouldReceive('getName')
            ->andReturn($queueName);
        $queue
            ->shouldReceive('delete')
            ->once()
            ->withNoArgs();
        return $queue;
    }

    /**
     * @param $message
     * @param $exchange
     * @return Mockery\MockInterface|ExchangeLocator
     */
    protected function mockExchangeLocator($message, $exchange)
    {
        $exchangeLocator = Mockery::mock(ExchangeLocator::class);
        $exchangeLocator
            ->shouldReceive('getExchangeForMessage')
            ->atLeast()->once()
            ->with($message)
            ->andReturn($exchange);
        return $exchangeLocator;
    }

    /**
     * @param $correlationId
     * @return Mockery\MockInterface
     */
    protected function mockResponseEnvelope($correlationId)
    {
        $envelope = Mockery::mock(\AMQPEnvelope::class);
        $envelope
            ->shouldReceive('getCorrelationId')
            ->atLeast()->once()
            ->andReturn($correlationId);
        return $envelope;
    }

    /**
     * @param $messageBody
     * @param $messageRoutingKey
     * @param $messageFlags
     * @param $messageAttributes
     * @return Mockery\MockInterface|Message
     */
    protected function mockMessage($messageBody, $messageRoutingKey, $messageFlags, array $messageAttributes = [])
    {
        $message = Mockery::mock(Message::class);
        $message
            ->shouldReceive('getMessage')
            ->andReturn($messageBody);
        $message
            ->shouldReceive('getRoutingKey')
            ->andReturn($messageRoutingKey);
        $message
            ->shouldReceive('getFlags')
            ->andReturn($messageFlags);
        $message
            ->shouldReceive('getAttributes')
            ->andReturn($messageAttributes);
        return $message;
    }

    /**
     * @param $messageAttributes
     * @param $queueName
     * @param $messageBody
     * @param $messageRoutingKey
     * @param $messageFlags
     * @param $isPublished
     * @return Mockery\MockInterface
     */
    protected function mockExchangeWithPublish($queueName, $messageBody, $messageRoutingKey, $messageFlags, array $messageAttributes = [], $isPublished)
    {
        $publishAttributes = $messageAttributes;
        $publishAttributes['reply_to'] = $queueName;
        $exchange = Mockery::mock(\AMQPExchange::class);
        $exchange
            ->shouldReceive('publish')
            ->once()
            ->with($messageBody, $messageRoutingKey, $messageFlags, $publishAttributes)
            ->andReturn($isPublished);

        return $exchange;
    }
}

class RemoteProcedureCommandPublisherPatched extends CommandPublisher
{
    private $queue;

    public function patchQueue(\AMQPQueue $queue)
    {
        $this->queue = $queue;
    }

    protected function declareResponseQueue(\AMQPExchange $exchange)
    {
        return $this->queue;
    }
}
