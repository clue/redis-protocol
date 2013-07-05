<?php

use Clue\Redis\Protocol\ProtocolBuffer;

class ProtocolBufferTest extends ProtocolBaseTest
{
    protected function createProtocol()
    {
        return new ProtocolBuffer();
    }
}
