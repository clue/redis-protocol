<?php

use Clue\Redis\Protocol\Parser\RecursiveParser;

class RecursiveParserTest extends AbstractParserTest
{
    protected function createProtocol()
    {
        return new RecursiveParser();
    }
}
