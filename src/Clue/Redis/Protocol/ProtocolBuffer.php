<?php

namespace Clue\Redis\Protocol;

use Clue\Redis\Protocol\ProtocolInterface;

/**
 * Simple redis wire protocol parser and serializer
 *
 * Heavily influenced by blocking parser implementation from jpd/redisent.
 *
 * @link https://github.com/jdp/redisent
 * @link http://redis.io/topics/protocol
 */
class ProtocolBuffer implements ProtocolInterface
{
    const CRLF = "\r\n";

    private $incomingBuffer = '';

    public function pushIncoming($dataChunk)
    {
        $this->incomingBuffer .= $dataChunk;
    }

    public function popIncoming()
    {

    }

    public function hasIncoming()
    {

    }

    public function createMessage(array $args)
    {
    	return sprintf('*%d%s%s%s', count($args), ProtocolBuffer::CRLF, implode(array_map(function($arg) {
            return sprintf('$%d%s%s', strlen($arg), ProtocolBuffer::CRLF, $arg);
        }, $args), ProtocolBuffer::CRLF), ProtocolBuffer::CRLF);
    }
}
