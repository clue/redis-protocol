<?php

use Clue\Redis\Protocol\Parser\ResponseParser;

class RecursiveParserTest extends AbstractParserTest
{
    protected function createProtocol()
    {
        return new ResponseParser();
    }
}
