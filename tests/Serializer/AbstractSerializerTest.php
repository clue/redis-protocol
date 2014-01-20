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
        //$this->assertEquals(":1\r\n", $this->serializer->createIntegerReply(1));
        $this->assertEquals(":0\r\n", $this->serializer->createReplyModel(0)->getSerialized());

        $this->assertEquals(":-12\r\n", $this->serializer->createReplyModel(-12.99)->getSerialized());
        $this->assertEquals(":14\r\n", $this->serializer->createReplyModel(14.99)->getSerialized());

        $this->assertEquals(":1\r\n", $this->serializer->createReplyModel(true)->getSerialized());
        $this->assertEquals(":0\r\n", $this->serializer->createReplyModel(false)->getSerialized());
    }

    public function testStringReply()
    {
        //$this->assertEquals("$4\r\ntest\r\n", $this->serializer->createBulkReply('test'));
        $this->assertEquals("$4\r\ntest\r\n", $this->serializer->createReplyModel('test')->getSerialized());
    }

    public function testNullBulkReply()
    {
        //$this->assertEquals("$-1\r\n", $this->serializer->createBulkReply(null));
        $this->assertEquals("$-1\r\n", $this->serializer->createReplyModel(null)->getSerialized());
    }

    /**
     * xx@depends testStringReply
     */
    public function testMultiBulkReply()
    {
        //$this->assertEquals("*1\r\n$4\r\ntest\r\n", $this->serializer->createMultiBulkReply(array('test')));
        $this->assertEquals("*1\r\n$4\r\ntest\r\n", $this->serializer->createReplyModel(array('test'))->getSerialized());

        //$this->assertEquals("*0\r\n", $this->serializer->createMultiBulkReply(array()));
        $this->assertEquals("*0\r\n", $this->serializer->createReplyModel(array())->getSerialized());
    }

    public function testNullMultiBulkReply()
    {
       // $this->assertEquals("*-1\r\n", $this->serializer->createMultiBulkReply(null));
    }

    public function testStatusReply()
    {
        //$this->assertEquals("+OK\r\n", $this->serializer->createStatusReply('OK'));
        //$this->assertEquals("+STATUS\r\n", $this->serializer->createStatusReply(new Status('STATUS')));
        //$this->assertEquals("+AUTO\r\n", $this->serializer->createReplyModel(new Status('AUTO')));
    }

    public function testErrorReply()
    {
        //$this->assertEquals("-ERR invalid\r\n", $this->serializer->createErrorReply('ERR invalid'));
        //$this->assertEquals("-WRONGTYPE invalid type\r\n", $this->serializer->createErrorReply(new ErrorReplyException('WRONGTYPE invalid type')));
        $this->assertEquals("-ERR failure\r\n", $this->serializer->createReplyModel(new Exception('ERR failure'))->getSerialized());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument()
    {
        $this->serializer->createReplyModel((object)array());
    }

//     public function testBenchCreateRequest()
//     {
//         for ($i = 0; $i < 100000; ++$i) {
//             $this->serializer->createReplyModel(array('a', 'b', 'c'));
//         }
//     }
}
