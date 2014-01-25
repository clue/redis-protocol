<?php

use Clue\Redis\Protocol\Model\BulkReply;

class BulkReplyTest extends AbstractModelTest
{
    protected function createModel($value)
    {
        return new BulkReply($value);
    }

    public function testStringReply()
    {
        $model = $this->createModel('test');

        $this->assertEquals('test', $model->getValueNative());
        $this->assertEquals("$4\r\ntest\r\n", $model->getMessageSerialized($this->serializer));
    }

    public function testEmptyStringReply()
    {
        $model = $this->createModel('');

        $this->assertEquals('', $model->getValueNative());
        $this->assertEquals("$0\r\n\r\n", $model->getMessageSerialized($this->serializer));
    }

    public function testIntegerCast()
    {
        $model = $this->createModel(123);

        $this->assertEquals('123', $model->getValueNative());
        $this->assertEquals("$3\r\n123\r\n", $model->getMessageSerialized($this->serializer));
    }

    public function testNullBulkReply()
    {
        $model = $this->createModel(null);

        $this->assertEquals(null, $model->getValueNative());
        $this->assertEquals("$-1\r\n", $model->getMessageSerialized($this->serializer));
    }
}
