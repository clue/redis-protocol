<?php

use Clue\Redis\Protocol\Parser\ResponseParser;

class RecursiveParserTest extends AbstractParserTest
{
    protected function createParser()
    {
        return new ResponseParser();
    }

    public function testPartialIncompleteBulkReply()
    {
        $this->assertEquals(array(), $this->parser->pushIncoming("$20\r\nincompl"));
    }

    public function testParsingStatusReplies()
    {
        // C: PING
        $message = "+PONG\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals('PONG', $data);

        // C: SET key value
        $message = "+OK\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals('OK', $data);
    }

    public function testParsingErrorReply()
    {
        $message = "-WRONGTYPE Operation against a key holding the wrong kind of value\r\n";

        $this->assertCount(1, $models = $this->parser->pushIncoming($message));
        $exception = reset($models);

        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ErrorReply', $exception);
        $this->assertEquals('WRONGTYPE Operation against a key holding the wrong kind of value', $exception->getMessage());
    }

    public function testParsingIntegerReply()
    {
        // C: INCR mykey
        $message = ":1\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(1, $data);
    }

    public function testParsingBulkReply()
    {
        // C: GET mykey
        $message = "$6\r\nfoobar\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals("foobar", $data);
    }

    public function testParsingNullBulkReply()
    {
        // C: GET nonexistingkey
        $message = "$-1\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(null, $data);
    }

    public function testParsingEmptyMultiBulkReply()
    {
        // C: LRANGE nokey 0 1
        $message = "*0\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array(), $data);
    }

    public function testParsingNullMultiBulkReply()
    {
        // C: BLPOP key 1
        $message = "*-1\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(null, $data);
    }

    public function testParsingMultiBulkReplyWithMixedElements()
    {
        $message = "*5\r\n:1\r\n:2\r\n:3\r\n:4\r\n$6\r\nfoobar\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array(1, 2, 3, 4, 'foobar'), $data);
    }

    public function testParsingMultiBulkReplyWithIncompletePush()
    {
        $this->assertCount(0, $this->parser->pushIncoming("*5\r\n:1\r\n:2\r"));
        $this->assertCount(1, $models = $this->parser->pushIncoming("\n:3\r\n:4\r\n$6\r\nfoobar\r\n"));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array(1, 2, 3, 4, 'foobar'), $data);
    }

    public function testParsingMultiBulkReplyWithNullElement()
    {
        $message = "*3\r\n$3\r\nfoo\r\n$-1\r\n$3\r\nbar\r\n";
        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $data = reset($models)->getValueNative();
        $this->assertEquals(array('foo', null, 'bar'), $data);
    }

    /**
     * @expectedException Clue\Redis\Protocol\Parser\ParserException
     */
    public function testParseError()
    {
        $this->parser->pushIncoming("invalid string\r\n");
    }
}
