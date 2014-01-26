<?php

use Clue\Redis\Protocol\Serializer\SerializerInterface;
use Clue\Redis\Protocol\Model\Status;
use Clue\Redis\Protocol\Model\ErrorReplyException;
//use Exception;

abstract class AbstractSerializerTest extends TestCase
{
    /**
     * @return SerializerInterface
     */
    abstract protected function createSerializer();

    public function setUp()
    {
        $this->serializer = $this->createSerializer();
    }

    public function testIntegerReply()
    {
        $model = $this->serializer->createReplyModel(0);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(0, $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(0));
    }

    public function testFloatCastIntegerReply()
    {
        $model = $this->serializer->createReplyModel(-12.99);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(-12, $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(-12.99));

        $model = $this->serializer->createReplyModel(14.99);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(14, $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(14.99));
    }

    public function testBooleanCastIntegerReply()
    {
        $model = $this->serializer->createReplyModel(true);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(1, $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(true));

        $model = $this->serializer->createReplyModel(false);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(0, $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(false));
    }

    public function testStringReply()
    {
        $model = $this->serializer->createReplyModel('test');
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\BulkReply', $model);
        $this->assertEquals('test', $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage('test'));
    }

    public function testNullCastNullBulkReply()
    {
        $model = $this->serializer->createReplyModel(null);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\BulkReply', $model);
        $this->assertEquals(null, $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(null));
    }

    public function testEmptyArrayMultiBulkReply()
    {
        $model = $this->serializer->createReplyModel(array());
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\MultiBulkReply', $model);
        $this->assertEquals(array(), $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(array()));
    }

    public function testArrayMultiBulkReply()
    {
        $model = $this->serializer->createReplyModel(array('test', 123));
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\MultiBulkReply', $model);
        $this->assertEquals(array('test', 123), $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(array('test', 123)));
    }

    public function testErrorReply()
    {
        $model = $this->serializer->createReplyModel(new Exception('ERR failure'));
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ErrorReply', $model);
        $this->assertEquals('ERR failure', $model->getValueNative());
        $this->assertEquals($model->getMessageSerialized($this->serializer), $this->serializer->getReplyMessage(new Exception('ERR failure')));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument()
    {
        $this->serializer->createReplyModel((object)array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidReplyData()
    {
        $this->serializer->getReplyMessage((object)array());
    }

    /**
     *
     * @param array $data
     * @dataProvider provideRequestMessage
     */
    public function testRequestMessage($command, $args)
    {
        // the model is already unit-tested, so just compare against its message
        $model = $this->serializer->createRequestModel($command, $args);

        $message = $this->serializer->getRequestMessage($command, $args);

        $this->assertEquals($model->getMessageSerialized($this->serializer), $message);
    }

    public function provideRequestMessage()
    {
        return array(
            array('PING', array()),
            array('GET', array('a')),
            array('SET', array('a', 'b')),
            array('SET', array('empty', ''))
        );
    }

//     public function testBenchCreateRequest()
//     {
//         for ($i = 0; $i < 100000; ++$i) {
//             $this->serializer->createReplyModel(array('a', 'b', 'c'));
//         }
//     }
}
