<?php

use Clue\Redis\Protocol\Model\MultiBulkReply;
use Clue\Redis\Protocol\Model\BulkReply;

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
        $this->assertEquals("*0\r\n", $model->getSerialized());
    }

    public function testNullMultiBulkReply()
    {
        $model = $this->createModel(null);

        $this->assertEquals(null, $model->getValueNative());
        $this->assertEquals("*-1\r\n", $model->getSerialized());
    }

    public function testSingleBulkEnclosed()
    {
        $model = $this->createModel(array(new BulkReply('test')));

        $this->assertEquals(array('test'), $model->getValueNative());
        $this->assertEquals("*1\r\n$4\r\ntest\r\n", $model->getSerialized());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConstructor()
    {
        $this->createModel(array('test'));
    }
}
