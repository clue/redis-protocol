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
        $this->assertEquals(":1\r\n", $this->serializer->createIntegerReply(1));
        $this->assertEquals(":0\r\n", $this->serializer->createReplyMessage(0));

        $this->assertEquals(":-12\r\n", $this->serializer->createReplyMessage(-12.99));
        $this->assertEquals(":14\r\n", $this->serializer->createReplyMessage(14.99));

        $this->assertEquals(":1\r\n", $this->serializer->createReplyMessage(true));
        $this->assertEquals(":0\r\n", $this->serializer->createReplyMessage(false));
    }

    public function testStringReply()
    {
        $this->assertEquals("$4\r\ntest\r\n", $this->serializer->createBulkReply('test'));
        $this->assertEquals("$4\r\ntest\r\n", $this->serializer->createReplyMessage('test'));
    }

    public function testNullBulkReply()
    {
        $this->assertEquals("$-1\r\n", $this->serializer->createBulkReply(null));
        $this->assertEquals("$-1\r\n", $this->serializer->createReplyMessage(null));
    }

    /**
     * xx@depends testStringReply
     */
    public function testMultiBulkReply()
    {
        $this->assertEquals("*1\r\n$4\r\ntest\r\n", $this->serializer->createMultiBulkReply(array('test')));
        $this->assertEquals("*1\r\n$4\r\ntest\r\n", $this->serializer->createReplyMessage(array('test')));

        $this->assertEquals("*0\r\n", $this->serializer->createMultiBulkReply(array()));
        $this->assertEquals("*0\r\n", $this->serializer->createReplyMessage(array()));
    }

    public function testNullMultiBulkReply()
    {
        $this->assertEquals("*-1\r\n", $this->serializer->createMultiBulkReply(null));
    }

    public function testStatusReply()
    {
        $this->assertEquals("+OK\r\n", $this->serializer->createStatusReply('OK'));
        $this->assertEquals("+STATUS\r\n", $this->serializer->createStatusReply(new Status('STATUS')));
        $this->assertEquals("+AUTO\r\n", $this->serializer->createReplyMessage(new Status('AUTO')));
    }

    public function testErrorReply()
    {
        $this->assertEquals("-ERR invalid\r\n", $this->serializer->createErrorReply('ERR invalid'));
        $this->assertEquals("-WRONGTYPE invalid type\r\n", $this->serializer->createErrorReply(new ErrorReplyException('WRONGTYPE invalid type')));
        $this->assertEquals("-ERR failure\r\n", $this->serializer->createReplyMessage(new Exception('ERR failure')));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument()
    {
        $this->serializer->createReplyMessage((object)array());
    }

//     public function testBenchCreateRequest()
//     {
//         for ($i = 0; $i < 100000; ++$i) {
//             $this->serializer->createReplyMessage(array('a', 'b', 'c'));
//         }
//     }
}
