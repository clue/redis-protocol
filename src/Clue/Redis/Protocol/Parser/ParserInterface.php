<?php

namespace Clue\Redis\Protocol\Parser;

use UnderflowException;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Parser\ParserException;

interface ParserInterface
{
    /**
     * push a chunk of the redis protocol response into the buffer
     *
     * @param string $dataChunk
     * @throws ParserException if the message can not be parsed
     * @see self::popIncomingModel()
     */
    public function pushIncoming($dataChunk);

    /**
     * parse the response in the incoming buffer and return a parsed message array
     *
     * @return ModelInterface
     * @throws UnderflowException if the incoming buffer does not contain a full response
     * @see self::pushIncoming() to add received data to the buffer
     * @see self::hasIncomingModel() to check if there's a complete message in the buffer and calling this method is safe
     */
    public function popIncomingModel();

    /**
     * check if there's (at least one) a complete message in the incoming buffer
     *
     * @return boolean
     * @see self::popIncomingModel()
     */
    public function hasIncomingModel();
}
