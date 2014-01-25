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
    }

    public function testSingleBulkEnclosed()
    {
        $model = $this->createModel(array(new BulkReply('test')));

        $this->assertEquals(array('test'), $model->getValueNative());
        $this->assertEquals("*1\r\n$4\r\ntest\r\n", $model->getMessageSerialized($this->serializer));

        $this->assertTrue($model->isRequest());
    }

    public function testMixedReply()
    {
        $model = $this->createModel(array(new BulkReply('test'), new IntegerReply(123)));

        $this->assertEquals(array('test', 123), $model->getValueNative());
        $this->assertEquals("*2\r\n$4\r\ntest\r\n:123\r\n", $model->getMessageSerialized($this->serializer));

        $this->assertFalse($model->isRequest());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConstructor()
    {
        $this->createModel(array('test'));
    }
}
