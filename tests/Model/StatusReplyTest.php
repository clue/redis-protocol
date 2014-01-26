<?php

use Clue\Redis\Protocol\Model\StatusReply;

class StatusReplyTest extends AbstractModelTest
{
    protected function createModel($value)
    {
        return new StatusReply($value);
    }

    public function testStatusOk()
    {
        $model = $this->createModel('OK');

        $this->assertEquals('OK', $model->getValueNative());
        $this->assertEquals("+OK\r\n", $model->getMessageSerialized($this->serializer));
    }
}
