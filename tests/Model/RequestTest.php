<?php

use Clue\Redis\Protocol\Model\Request;

class RequestTest extends AbstractModelTest
{
    protected function createModel($value)
    {
        return new Request('QUIT');
    }

    public function testPing()
    {
        $model = new Request('PING');

        $this->assertEquals('PING', $model->getCommand());
        $this->assertEquals(array(), $model->getArgs());
        $this->assertEquals(array('PING'), $model->getValueNative());
        $this->assertEquals("*1\r\n$4\r\nPING\r\n", $model->getMessageSerialized($this->serializer));

        $reply = $model->getReplyModel();
        $this->assertEquals($model->getValueNative(), $reply->getValueNative());
    }

    public function testGet()
    {
        $model = new Request('GET', array('a'));

        $this->assertEquals('GET', $model->getCommand());
        $this->assertEquals(array('a'), $model->getArgs());
        $this->assertEquals(array('GET', 'a'), $model->getValueNative());
        $this->assertEquals("*2\r\n$3\r\nGET\r\n$1\r\na\r\n", $model->getMessageSerialized($this->serializer));

        $reply = $model->getReplyModel();
        $this->assertEquals($model->getValueNative(), $reply->getValueNative());
    }
}
