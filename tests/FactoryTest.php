<?php

use Clue\Redis\Protocol\Factory;

class FactoryTest extends TestCase
{
    public function testCreate()
    {
        $protocol = Factory::create();

        $this->assertInstanceOf('Clue\Redis\Protocol\ProtocolInterface', $protocol);
    }
}
