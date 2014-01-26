<?php

use Clue\Redis\Protocol\Model\ErrorReply;

class ErrorReplyTest extends AbstractModelTest
{
    protected function createModel($value)
    {
        return new ErrorReply($value);
    }

    public function testError()
    {
        $model = $this->createModel('ERR error');

        $this->assertEquals('ERR error', $model->getValueNative());
        $this->assertEquals("-ERR error\r\n", $model->getMessageSerialized($this->serializer));
    }
}
