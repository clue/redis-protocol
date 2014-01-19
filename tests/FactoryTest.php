<?php

use Clue\Redis\Protocol\Factory;

class FactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new Factory();
    }

    public function testCreateParser()
    {
        $parser = $this->factory->createParser();

        $this->assertInstanceOf('Clue\Redis\Protocol\Parser\ParserInterface', $parser);
    }

    public function testCreateSerializer()
    {
        $serializer = $this->factory->createSerializer();

        $this->assertInstanceOf('Clue\Redis\Protocol\Serializer\SerializerInterface', $serializer);
    }
}
