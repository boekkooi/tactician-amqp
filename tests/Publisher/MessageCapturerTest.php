<?php
namespace Tests\Boekkooi\Tactician\AMQP\Publisher;

use Boekkooi\Tactician\AMQP\Publisher\MessageCapturer;
use Tests\Boekkooi\Tactician\AMQP\Fixtures\Command\MessageCommand;
use Mockery;

class MessageCapturerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageCapturer
     */
    private $publisher;

    public function setUp()
    {
        $this->publisher = new MessageCapturer();
    }

    /**
     * @test
     */
    public function it_should_capture_messages()
    {
        $message1 = new MessageCommand('message', 'key');
        $message2 = new MessageCommand('message', 'key');
        $expectedMessage = [ $message1, $message2 ];

        $this->publisher->publish($message1);
        $this->publisher->publish($message2);

        $this->assertSame($expectedMessage, $this->publisher->fetchMessages());

        // It clears message after the fetch
        $this->assertSame([], $this->publisher->fetchMessages());
    }

    /**
     * @test
     */
    public function it_should_clear_capture_messages()
    {
        $message1 = new MessageCommand('message', 'key');
        $message2 = new MessageCommand('message', 'key');
        $expectedMessage = [ $message1, $message2 ];

        $this->publisher->publish($message1);
        $this->publisher->publish($message2);

        // Avoid fetch messages from clearing
        $this->assertSame($expectedMessage, $this->publisher->fetchMessages(false));
        $this->assertSame($expectedMessage, $this->publisher->fetchMessages(false));

        // Clear the messages
        $this->publisher->clear();

        $this->assertSame([], $this->publisher->fetchMessages());
    }
}
