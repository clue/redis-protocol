<?php

use Clue\Redis\Protocol\ProtocolInterface;
// use UnderflowException;

abstract class ProtocolBaseTest extends TestCase
{
    /**
     *
     * @var ProtocolInterface
     */
    protected $protocol;

    abstract protected function createProtocol();

    public function setUp()
    {
        $this->protocol = $this->createProtocol();
        $this->assertInstanceOf('Clue\Redis\Protocol\ProtocolInterface', $this->protocol);
    }

    public function testEmptyHasNoIncoming()
    {
        $this->assertFalse($this->protocol->hasIncoming());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testEmptyPopThrowsException()
    {
        $this->protocol->popIncoming();
    }
}
