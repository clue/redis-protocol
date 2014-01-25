<?php

use Clue\Redis\Protocol\Parser\ParserInterface;
use Clue\Redis\Protocol\Serializer\RecursiveSerializer;
// use UnderflowException;

abstract class AbstractParserTest extends TestCase
{
    /**
     *
     * @var ParserInterface
     */
    protected $protocol;

    abstract protected function createProtocol();

    protected function createMessage($data)
    {
        $serializer = new RecursiveSerializer();

        return $serializer->getRequestMessage($data);
    }

    public function setUp()
    {
        $this->protocol = $this->createProtocol();
        $this->assertInstanceOf('Clue\Redis\Protocol\Parser\ParserInterface', $this->protocol);
    }

    public function testEmptyHasNoIncoming()
    {
        $this->assertFalse($this->protocol->hasIncomingModel());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testEmptyPopThrowsException()
    {
        $this->protocol->popIncomingModel();
    }

    public function testCreateMessageOne()
    {
        $message = $this->createMessage(array(
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

        $this->assertTrue($this->protocol->hasIncomingModel());

        $this->assertEquals(array('test'), $this->protocol->popIncomingModel()->getValueNative());

        $this->assertFalse($this->protocol->hasIncomingModel());
    }

    public function testPartialIncompleteBulkReply()
    {
        $this->protocol->pushIncoming("$20\r\nincompl");
        $this->assertFalse($this->protocol->hasIncomingModel());
    }

    public function testCreateMessageTwo()
    {
        $message = $this->createMessage(array(
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

        $this->assertTrue($this->protocol->hasIncomingModel());

        $this->assertEquals(array('test', 'second'), $this->protocol->popIncomingModel()->getValueNative());

        $this->assertFalse($this->protocol->hasIncomingModel());
    }

    public function testParsingStatusReplies()
    {
        // C: PING
        $message = "+PONG\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals('PONG', $data);

        // C: SET key value
        $message = "+OK\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals('OK', $data);
    }

    public function testParsingErrorReply()
    {
        $message = "-WRONGTYPE Operation against a key holding the wrong kind of value\r\n";

        $this->protocol->pushIncoming($message);
        $exception = $this->protocol->popIncomingModel();

        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ErrorReply', $exception);
        $this->assertEquals('WRONGTYPE Operation against a key holding the wrong kind of value', $exception->getMessage());
    }

    public function testParsingIntegerReply()
    {
        // C: INCR mykey
        $message = ":1\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals(1, $data);
    }

    public function testParsingBulkReply()
    {
        // C: GET mykey
        $message = "$6\r\nfoobar\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals("foobar", $data);
    }

    public function testParsingNullBulkReply()
    {
        // C: GET nonexistingkey
        $message = "$-1\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals(null, $data);
    }

    public function testParsingEmptyMultiBulkReply()
    {
        // C: LRANGE nokey 0 1
        $message = "*0\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals(array(), $data);
    }

    public function testParsingNullMultiBulkReply()
    {
        // C: BLPOP key 1
        $message = "*-1\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals(null, $data);
    }

    public function testParsingMultiBulkReplyWithMixedElements()
    {
        $message = "*5\r\n:1\r\n:2\r\n:3\r\n:4\r\n$6\r\nfoobar\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals(array(1, 2, 3, 4, 'foobar'), $data);
    }

    public function testParsingMultiBulkReplyWithNullElement()
    {
        $message = "*3\r\n$3\r\nfoo\r\n$-1\r\n$3\r\nbar\r\n";
        $this->protocol->pushIncoming($message);

        $data = $this->protocol->popIncomingModel()->getValueNative();
        $this->assertEquals(array('foo', null, 'bar'), $data);
    }

    /**
     * @expectedException Clue\Redis\Protocol\Parser\ParserException
     */
    public function testParseError()
    {
        $this->protocol->pushIncoming("invalid string\r\n");
    }
}
