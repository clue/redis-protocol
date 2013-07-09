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

    public function testParsingStatusReplies()
    {
        // C: PING
        $message = "+PONG\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals('PONG', $data);

        // C: SET key value
        $message = "+OK\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals('OK', $data);
    }

    public function testParsingErrorReply()
    {
        $message = "-WRONGTYPE Operation against a key holding the wrong kind of value\r\n";

        $this->protocol->pushIncoming($message);
        $exception = $this->protocol->popIncoming();

        $this->assertInstanceOf('Clue\Redis\Protocol\ErrorReplyException', $exception);
        $this->assertEquals('WRONGTYPE Operation against a key holding the wrong kind of value', $exception->getMessage());
    }

    public function testParsingIntegerReply()
    {
        // C: INCR mykey
        $message = ":1\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals(1, $data);
    }

    public function testParsingBulkReply()
    {
        // C: GET mykey
        $message = "$6\r\nfoobar\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals("foobar", $data);
    }

    public function testParsingNullBulkReply()
    {
        // C: GET nonexistingkey
        $message = "$-1\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals(null, $data);
    }

    public function testParsingEmptyMultiBulkReply()
    {
        // C: LRANGE nokey 0 1
        $message = "*0\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals(array(), $data);
    }

    public function testParsingNullMultiBulkReply()
    {
        // C: BLPOP key 1
        $message = "*-1\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals(null, $data);
    }

    public function testParsingMultiBulkReplyWithMixedElements()
    {
        $message = "*5\r\n:1\r\n:2\r\n:3\r\n:4\r\n$6\r\nfoobar\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals(array(1, 2, 3, 4, 'foobar'), $data);
    }

    public function testParsingMultiBulkReplyWithNullElement()
    {
        $message = "*3\r\n$3\r\nfoo\r\n$-1\r\n$3\r\nbar\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncoming();
        $this->assertEquals(array('foo', null, 'bar'), $data);
    }

    /**
     * @expectedException Clue\Redis\Protocol\ParserException
     */
    public function testParseError()
    {
        $this->protocol->pushIncoming("invalid string\r\n");
    }
}
