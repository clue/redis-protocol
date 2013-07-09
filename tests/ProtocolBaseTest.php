<?php

use Clue\Redis\Protocol\ProtocolInterface;
// use UnderflowException;

abstract class ProtocolBaseTest extends TestCase
{
    /**
     *
     * @var ProtocolInterface
     */
    protected $protocol;

    abstract protected function createProtocol();

    public function setUp()
    {
        $this->protocol = $this->createProtocol();
        $this->assertInstanceOf('Clue\Redis\Protocol\ProtocolInterface', $this->protocol);
    }

    public function testEmptyHasNoIncoming()
    {
        $this->assertFalse($this->protocol->hasIncoming());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testEmptyPopThrowsException()
    {
        $this->protocol->popIncoming();
    }

    public function testCreateMessageOne()
    {
        $message = $this->protocol->createMessage(array(
            'test'
        ));

        $expected = "*1\r\n$4\r\ntest\r\n";
        $this->assertEquals($expected, $message);
    }

    public function testCreateMessageTwo()
    {
        $message = $this->protocol->createMessage(array(
            'test',
            'second'
        ));

        $expected = "*2\r\n$4\r\ntest\r\n$6\r\nsecond\r\n";
        $this->assertEquals($expected, $message);
    }
}
