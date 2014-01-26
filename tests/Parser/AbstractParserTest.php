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
