<?php

namespace Clue\Tests\Redis\Protocol\Serializer;

use Clue\Redis\Protocol\Serializer\RecursiveSerializer;

class RecursiveSerializerTest extends AbstractSerializerTest
{
    protected function createSerializer()
    {
        return new RecursiveSerializer();
    }
}
