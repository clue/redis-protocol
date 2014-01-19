<?php

use Clue\Redis\Protocol\Factory;

class FactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new Factory();

        $protocol = $factory->createParser();

        $this->assertInstanceOf('Clue\Redis\Protocol\Parser\ParserInterface', $protocol);
    }
}
