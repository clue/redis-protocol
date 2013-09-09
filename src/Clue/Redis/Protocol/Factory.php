<?php

namespace Clue\Redis\Protocol;

use Clue\Redis\Protocol\ProtocolInterface;
use Clue\Redis\Protocol\ProtocolBuffer;

/**
 * Simple static factory method used to instanciate the best available protocol implementation
 */
class Factory
{
    /**
     * instantiate the best available protocol implementation
     *
     * @return ProtocolInterface
     */
    public static function create()
    {
        return new ProtocolBuffer();
    }
}
