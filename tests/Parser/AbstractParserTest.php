<?php

use Clue\Redis\Protocol\Parser\ParserInterface;
use Clue\Redis\Protocol\Serializer\RecursiveSerializer;
use Clue\Redis\Protocol\Parser\MessageBuffer;

abstract class AbstractParserTest extends TestCase
{
    /**
     *
     * @var ParserInterface
     */
    protected $protocol;

    abstract protected function createProtocol();

    public function setUp()
    {
        $this->protocol = $this->createProtocol();
        $this->assertInstanceOf('Clue\Redis\Protocol\Parser\ParserInterface', $this->protocol);
    }

    public function testParsingMessageOne()
    {
        // getRequestMessage('test')
        $message = $expected = "*1\r\n$4\r\ntest\r\n";

        $models = $this->protocol->pushIncoming($message);
        $this->assertCount(1, $models);

        $model = reset($models);
        $this->assertEquals(array('test'), $model->getValueNative());
    }

    public function testPartialIncompleteBulkReply()
    {
        $this->assertEquals(array(), $this->protocol->pushIncoming("$20\r\nincompl"));
    }

    public function testParsingMessageTwoPartial()
    {
        // getRequestMessage('test', array('second'))
        $message = "*2\r\n$4\r\ntest\r\n$6\r\nsecond\r\n";

        $this->assertEquals(array(), $this->protocol->pushIncoming(substr($message, 0, 1)));
        $this->assertEquals(array(), $this->protocol->pushIncoming(substr($message, 1, 1)));
        $this->assertEquals(array(), $this->protocol->pushIncoming(substr($message, 2, 1)));
        $this->assertEquals(array(), $this->protocol->pushIncoming(substr($message, 3, 10)));
        $this->assertCount(1, $models = $this->protocol->pushIncoming(substr($message, 13)));

        $model = reset($models);

        $this->assertEquals(array('test', 'second'), $model->getValueNative());
    }

    public function testParsingStatusReplies()
    {
        // C: PING
        $message = "+PONG\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals('PONG', $data);

        // C: SET key value
        $message = "+OK\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals('OK', $data);
    }

    public function testParsingErrorReply()
    {
        $message = "-WRONGTYPE Operation against a key holding the wrong kind of value\r\n";

        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));
        $exception = reset($models);

        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ErrorReply', $exception);
        $this->assertEquals('WRONGTYPE Operation against a key holding the wrong kind of value', $exception->getMessage());
    }

    public function testParsingIntegerReply()
    {
        // C: INCR mykey
        $message = ":1\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(1, $data);
    }

    public function testParsingBulkReply()
    {
        // C: GET mykey
        $message = "$6\r\nfoobar\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals("foobar", $data);
    }

    public function testParsingNullBulkReply()
    {
        // C: GET nonexistingkey
        $message = "$-1\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(null, $data);
    }

    public function testParsingEmptyMultiBulkReply()
    {
        // C: LRANGE nokey 0 1
        $message = "*0\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array(), $data);
    }

    public function testParsingNullMultiBulkReply()
    {
        // C: BLPOP key 1
        $message = "*-1\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(null, $data);
    }

    public function testParsingMultiBulkReplyWithMixedElements()
    {
        $message = "*5\r\n:1\r\n:2\r\n:3\r\n:4\r\n$6\r\nfoobar\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array(1, 2, 3, 4, 'foobar'), $data);
    }

    public function testParsingMultiBulkReplyWithNullElement()
    {
        $message = "*3\r\n$3\r\nfoo\r\n$-1\r\n$3\r\nbar\r\n";
        $this->assertCount(1, $models = $this->protocol->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array('foo', null, 'bar'), $data);
    }

    /**
     * @expectedException Clue\Redis\Protocol\Parser\ParserException
     */
    public function testParseError()
    {
        $this->protocol->pushIncoming("invalid string\r\n");
    }

    public function testMessageBuffer()
    {
        $buffer = new MessageBuffer($this->protocol);

        $this->assertFalse($buffer->hasIncomingModel());

        $data = "*1\r\n$4\r\ntest\r\n";
        $this->assertCount(1, $models = $buffer->pushIncoming($data));
        $this->assertTrue($buffer->hasIncomingModel());

        $expected = reset($models);
        $this->assertSame($expected, $buffer->popIncomingModel());
        $this->assertFalse($buffer->hasIncomingModel());

        $this->setExpectedException('UnderflowException');
        $buffer->popIncomingModel();
    }
}
