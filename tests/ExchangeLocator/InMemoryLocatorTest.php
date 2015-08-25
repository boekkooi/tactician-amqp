<?php
namespace Tests\Boekkooi\Tactician\AMQP\ExchangeLocator;

use Boekkooi\Tactician\AMQP\Exception\MissingExchangeException;
use Boekkooi\Tactician\AMQP\ExchangeLocator\InMemoryLocator;
use Tests\Boekkooi\Tactician\AMQP\Fixtures\Command\AnotherMessageCommand;
use Tests\Boekkooi\Tactician\AMQP\Fixtures\Command\MessageCommand;
use Mockery;

class InMemoryLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryLocator
     */
    private $inMemoryLocator;

    public function setUp()
    {
        $this->inMemoryLocator = new InMemoryLocator();
    }

    /**
     * @test
     */
    public function it_should_return_the_exchange_for_a_specific_message()
    {
        /** @var \AMQPExchange $exchange */
        $exchange = Mockery::mock(\AMQPExchange::class);

        $this->inMemoryLocator->addExchange($exchange, MessageCommand::class);

        $this->assertSame(
            $exchange,
            $this->inMemoryLocator->getExchangeForMessage(new MessageCommand('msg'))
        );
    }

    /**
     * @test
     */
    public function it_can_be_created_with_a_map_of_message_classes()
    {
        $exchange = Mockery::mock(\AMQPExchange::class);
        $anotherExchange = Mockery::mock(\AMQPExchange::class);

        $commandToHandlerMap = [
            MessageCommand::class => $exchange,
            AnotherMessageCommand::class => $anotherExchange
        ];

        $locator = new InMemoryLocator($commandToHandlerMap);

        $this->assertSame(
            $commandToHandlerMap[MessageCommand::class],
            $locator->getExchangeForMessage(new MessageCommand('first'))
        );

        $this->assertSame(
            $commandToHandlerMap[AnotherMessageCommand::class],
            $locator->getExchangeForMessage(new AnotherMessageCommand('second'))
        );
    }

    /**
     * @test
     */
    public function it_should_throw_a_exception_when_no_exchange_exists_for_a_message()
    {
        $this->setExpectedException(MissingExchangeException::class);

        $this->inMemoryLocator->getExchangeForMessage(new MessageCommand('first'));
    }
}
