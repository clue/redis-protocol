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
    }

    public function testFloatCastIntegerReply()
    {
        $model = $this->serializer->createReplyModel(-12.99);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(-12, $model->getValueNative());

        $model = $this->serializer->createReplyModel(14.99);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(14, $model->getValueNative());
    }

    public function testBooleanCastIntegerReply()
    {
        $model = $this->serializer->createReplyModel(true);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(1, $model->getValueNative());

        $model = $this->serializer->createReplyModel(false);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\IntegerReply', $model);
        $this->assertEquals(0, $model->getValueNative());
    }

    public function testStringReply()
    {
        $model = $this->serializer->createReplyModel('test');
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\BulkReply', $model);
        $this->assertEquals('test', $model->getValueNative());
    }

    public function testNullCastNullBulkReply()
    {
        $model = $this->serializer->createReplyModel(null);
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\BulkReply', $model);
        $this->assertEquals(null, $model->getValueNative());
    }

    public function testEmptyArrayMultiBulkReply()
    {
        $model = $this->serializer->createReplyModel(array());
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\MultiBulkReply', $model);
        $this->assertEquals(array(), $model->getValueNative());
    }

    public function testArrayMultiBulkReply()
    {
        $model = $this->serializer->createReplyModel(array('test', 123));
        $this->assertInstanceOf('Clue\Redis\Protocol\Model\MultiBulkReply', $model);
        $this->assertEquals(array('test', 123), $model->getValueNative());
    }

    public function testErrorReply()
    {
        $this->assertEquals("-ERR failure\r\n", $this->serializer->createReplyModel(new Exception('ERR failure'))->getMessageSerialized());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument()
    {
        $this->serializer->createReplyModel((object)array());
    }

    /**
     *
     * @param array $data
     * @dataProvider provideRequestMessage
     */
    public function testRequestMessage($data)
    {
        // the model is already unit-tested, so just compare against its message
        $model = $this->serializer->createRequestModel($data);

        $message = $this->serializer->createRequestMessage($data);

        $this->assertEquals($model->getMessageSerialized(), $message);
    }

    public function provideRequestMessage()
    {
        return array(
            array(array('PING')),
            array(array('GET', 'a')),
            array(array('SET', 'a', 'b')),
            array(array('SET', 'empty', ''))
        );
    }

//     public function testBenchCreateRequest()
//     {
//         for ($i = 0; $i < 100000; ++$i) {
//             $this->serializer->createReplyModel(array('a', 'b', 'c'));
//         }
//     }
}
