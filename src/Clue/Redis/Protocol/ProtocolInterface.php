<?php

namespace Clue\Redis\Protocol;

use UnderflowException;

interface ProtocolInterface
{
    /**
     * push a chunk of the redis protocol response into the buffer
     *
     * @param string $dataChunk
     * @see self::popIncoming()
     */
    public function pushIncoming($dataChunk);

    /**
     * parse the response in the incoming buffer and return a parsed message array
     *
     * @return array
     * @throws UnderflowException if the incoming buffer does not contain a full response
     * @see self::pushIncoming() to add received data to the buffer
     * @see self::hasIncoming() to check if there's a complete message in the buffer and calling this method is safe
     */
    public function popIncoming();

    /**
     * check if there's (at least one) a complete message in the incoming buffer
     *
     * @return boolean
     * @see self::popIncoming()
     */
    public function hasIncoming();

    public function createMessage(array $parts);
}
