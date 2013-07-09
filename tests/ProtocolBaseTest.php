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

        return $message;
    }

    /**
     * @param string $message
     * @depends testCreateMessageOne
     */
    public function testParsingMessageOne($message)
    {
        $this->protocol->pushIncoming($message);

        $this->assertTrue($this->protocol->hasIncoming());

        $this->assertEquals(array('test'), $this->protocol->popIncoming());

        $this->assertFalse($this->protocol->hasIncoming());
    }

    public function testCreateMessageTwo()
    {
        $message = $this->protocol->createMessage(array(
            'test',
            'second'
        ));

        $expected = "*2\r\n$4\r\ntest\r\n$6\r\nsecond\r\n";
        $this->assertEquals($expected, $message);

        return $message;
    }

    /**
     * @param string $message
     * @depends testCreateMessageTwo
     */
    public function testParsingMessageTwoPartial($message)
    {
        $this->protocol->pushIncoming(substr($message, 0, 1));
        $this->protocol->pushIncoming(substr($message, 1, 1));
        $this->protocol->pushIncoming(substr($message, 2, 1));
        $this->protocol->pushIncoming(substr($message, 3, 10));
        $this->protocol->pushIncoming(substr($message, 13));

        $this->assertTrue($this->protocol->hasIncoming());

        $this->assertEquals(array('test', 'second'), $this->protocol->popIncoming());

        $this->assertFalse($this->protocol->hasIncoming());
    }

    public function testParsingErrorReply()
    {
        $message = "-WRONGTYPE Operation against a key holding the wrong kind of value\r\n";

        $this->protocol->pushIncoming($message);
        $exception = $this->protocol->popIncoming();

        $this->assertInstanceOf('Clue\Redis\Protocol\ErrorReplyException', $exception);
        $this->assertEquals('WRONGTYPE Operation against a key holding the wrong kind of value', $exception->getMessage());
    }
}
