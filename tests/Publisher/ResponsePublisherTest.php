<?php
namespace Tests\Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\AMQPCommand;
use Boekkooi\Tactician\AMQP\Exception\FailedToPublishException;
use Boekkooi\Tactician\AMQP\Exception\InvalidArgumentException;
use Boekkooi\Tactician\AMQP\Message;
use Boekkooi\Tactician\AMQP\Publisher\ResponsePublisher;
use Mockery;
use Tests\Boekkooi\Tactician\AMQP\Fixtures\Command\MessageCommand;

class ResponsePublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_publish_a_response_message()
    {
        // Prep
        $channel = Mockery::mock(\AMQPChannel::class);
        $command = $this->mockCommand('a-reply-id', 'correlation-id', $channel);
        $returnMessage = new MessageCommand('[ "data" ]', 'unused-key', AMQP_AUTOACK, ['content_type' => 'application/json']);

        $exchangeMock = Mockery::mock(\AMQPExchange::class);
        $exchangeMock
            ->shouldReceive('publish')
            ->once()
            ->withArgs([
                '[ "data" ]',
                'a-reply-id',
                AMQP_AUTOACK,
                [ 'content_type' => 'application/json', 'correlation_id' => 'correlation-id' ]
            ])
            ->andReturn(true);

        // Test
        $publisher = new ResponsePublisherPatched($command);
        $publisher->patchExchange($exchangeMock);
        $publisher->publish($returnMessage);
    }

    /**
     * @test
     */
    public function it_requires_a_reply_to()
    {
        $command = $this->mockCommand('', '', Mockery::mock(\AMQPChannel::class));

        $this->setExpectedException(InvalidArgumentException::class, 'reply-to');
        new ResponsePublisher($command);
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_publishing_fails()
    {
        // Prep
        $channel = Mockery::mock(\AMQPChannel::class);
        $command = $this->mockCommand('a-reply-id', 'some-id', $channel);
        $returnMessage = new MessageCommand('data');

        $exchangeMock = Mockery::mock(\AMQPExchange::class);
        $exchangeMock
            ->shouldReceive('publish')
            ->once()
            ->withArgs([
                'data',
                'a-reply-id',
                AMQP_IMMEDIATE,
                [ 'correlation_id' => 'some-id' ]
            ])
            ->andReturn(false);

        $publisher = new ResponsePublisherPatched($command);
        $publisher->patchExchange($exchangeMock);

        // Test
        $this->setExpectedException(FailedToPublishException::class);
        $publisher->publish($returnMessage);
    }

    protected function mockCommand($replyTo, $correlationId, $channel)
    {
        $envelope = Mockery::mock(\AMQPEnvelope::class);
        $envelope
            ->shouldReceive('getReplyTo')
            ->andReturn($replyTo);
        $envelope
            ->shouldReceive('getCorrelationId')
            ->andReturn($correlationId);

        $queue = Mockery::mock(\AMQPQueue::class);
        $queue
            ->shouldReceive('getChannel')
            ->andReturn($channel);

        return new AMQPCommand($envelope, $queue);
    }
}

class ResponsePublisherPatched extends ResponsePublisher
{
    private $exchange = null;

    /**
     * @param Mockery\MockInterface|\AMQPExchange $exchange
     */
    public function patchExchange($exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @inheritdoc
     */
    protected function getExchange(Message $message)
    {
        return $this->exchange;
    }
}
