<?php

use Clue\Redis\Protocol\Model\IntegerReply;

class IntegerReplyTest extends AbstractModelTest
{
    protected function createModel($value)
    {
        return new IntegerReply($value);
    }

    public function testIntegerReply()
    {
        $model = $this->createModel(0);
        $this->assertEquals(0, $model->getValueNative());
        $this->assertEquals(":0\r\n", $model->getMessageSerialized($this->serializer));
    }

    public function testFloatCasted()
    {
        $model = $this->createModel(-12.99);
        $this->assertEquals(-12, $model->getValueNative());
        $this->assertEquals(":-12\r\n", $model->getMessageSerialized($this->serializer));

        $model = $this->createModel(14.99);
        $this->assertEquals(14, $model->getValueNative());
        $this->assertEquals(":14\r\n", $model->getMessageSerialized($this->serializer));
    }

    public function testBooleanCasted()
    {
        $model = $this->createModel(true);
        $this->assertEquals(1, $model->getValueNative());
        $this->assertEquals(":1\r\n", $model->getMessageSerialized($this->serializer));

        $model = $this->createModel(false);
        $this->assertEquals(0, $model->getValueNative());
        $this->assertEquals(":0\r\n", $model->getMessageSerialized($this->serializer));
    }
}
