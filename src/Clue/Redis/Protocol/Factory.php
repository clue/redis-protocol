<?php

namespace Clue\Redis\Protocol;

use Clue\Redis\Protocol\Parser\ParserInterface;
use Clue\Redis\Protocol\Parser\ResponseParser;
use Clue\Redis\Protocol\Serializer\SerializerInterface;
use Clue\Redis\Protocol\Serializer\RecursiveSerializer;

/**
 * Provides factory methods used to instantiate the best available protocol implementation
 */
class Factory
{
    /**
     * instantiate the best available protocol parser implementation
     *
     * @return ParserInterface
     */
    public function createResponseParser()
    {
        return new ResponseParser();
    }

    /**
     * instantiate the best available protocol serializer implementation
     *
     * @return SerializerInterface
     */
    public function createSerializer()
    {
        return new RecursiveSerializer();
    }
}
