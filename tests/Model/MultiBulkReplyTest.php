<?php

use Clue\Redis\Protocol\Model\MultiBulkReply;
use Clue\Redis\Protocol\Model\BulkReply;
use Clue\Redis\Protocol\Model\IntegerReply;

class MultiBulkReplyTest extends AbstractModelTest
{
    protected function createModel($value)
    {
        return new MultiBulkReply($value);
    }

    public function testEmptyArray()
    {
        $model = $this->createModel(array());

        $this->assertEquals(array(), $model->getValueNative());
        $this->assertEquals("*0\r\n", $model->getMessageSerialized($this->serializer));

        $this->assertFalse($model->isRequest());
    }

    public function testNullMultiBulkReply()
    {
        $model = $this->createModel(null);

        $this->assertEquals(null, $model->getValueNative());
        $this->assertEquals("*-1\r\n", $model->getMessageSerialized($this->serializer));

        $this->assertFalse($model->isRequest());

        return $model;
    }

    /**
     * @param MultiBulkReply $model
     * @depends testNullMultiBulkReply
     * @expectedException UnexpectedValueException
     */
    public function testNullMultiBulkReplyIsNotARequest(MultiBulkReply $model)
    {
        $model->getRequestModel();
    }

    public function testSingleBulkEnclosed()
    {
        $model = $this->createModel(array(new BulkReply('test')));

        $this->assertEquals(array('test'), $model->getValueNative());
        $this->assertEquals("*1\r\n$4\r\ntest\r\n", $model->getMessageSerialized($this->serializer));

        $this->assertTrue($model->isRequest());

        // this can be represented by a request
        $request = $model->getRequestModel();
        $this->assertEquals($model->getValueNative(), $request->getValueNative());

        // representing the request as a reply should return our original instance
        $reply = $request->getReplyModel();
        $this->assertEquals($model, $reply);

        return $model;
    }

    /**
     * @depends testSingleBulkEnclosed
     */
    public function testStringEnclosedEqualsSingleBulk(MultiBulkReply $expected)
    {
        $model = $this->createModel(array('test'));

        $this->assertEquals($expected->getValueNative(), $model->getValueNative());
        $this->assertEquals($expected->getMessageSerialized($this->serializer), $model->getMessageSerialized($this->serializer));

        $this->assertTrue($model->isRequest());
    }

    public function testMixedReply()
    {
        $model = $this->createModel(array(new BulkReply('test'), new IntegerReply(123)));

        $this->assertEquals(array('test', 123), $model->getValueNative());
        $this->assertEquals("*2\r\n$4\r\ntest\r\n:123\r\n", $model->getMessageSerialized($this->serializer));

        $this->assertFalse($model->isRequest());

        return $model;
    }

    /**
     * @param MultiBulkReply $model
     * @depends testMixedReply
     * @expectedException UnexpectedValueException
     */
    public function testMixedReplyIsNotARequest(MultiBulkReply $model)
    {
        $model->getRequestModel();
    }

    public function testMultiStrings()
    {
        $model = $this->createModel(array('SET', 'a', 'b'));

        $this->assertEquals(array('SET', 'a', 'b'), $model->getValueNative());

        $this->assertTrue($model->isRequest());

        $request = $model->getRequestModel();

        // this can be represented by a request
        $request = $model->getRequestModel();
        $this->assertEquals($model->getValueNative(), $request->getValueNative());
    }
}
