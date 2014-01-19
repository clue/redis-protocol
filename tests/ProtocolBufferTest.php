<?php

use Clue\Redis\Protocol\Parser\RecursiveParser;

class ProtocolBufferTest extends ProtocolBaseTest
{
    protected function createProtocol()
    {
        return new RecursiveParser();
    }
}
