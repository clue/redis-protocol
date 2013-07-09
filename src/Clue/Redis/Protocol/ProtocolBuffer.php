<?php

namespace Clue\Redis\Protocol;

use Clue\Redis\Protocol\ProtocolInterface;
use UnderflowException;

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
    private $incomingQueue = array();

    public function pushIncoming($dataChunk)
    {
        $this->incomingBuffer .= $dataChunk;

        $this->tryParsingIncomingMessages();
    }

    public function popIncoming()
    {
        $message = array_shift($this->incomingQueue);
        if ($message === null) {
            throw new UnderflowException('Incoming message queue is empty');
        }
        return $message;
    }

    public function hasIncoming()
    {
        return ($this->incomingQueue) ? true : false;
    }

    public function createMessage(array $args)
    {
    	return sprintf('*%d%s%s%s', count($args), ProtocolBuffer::CRLF, implode(array_map(function($arg) {
            return sprintf('$%d%s%s', strlen($arg), ProtocolBuffer::CRLF, $arg);
        }, $args), ProtocolBuffer::CRLF), ProtocolBuffer::CRLF);
    }

    private function tryParsingIncomingMessages()
    {

    }
}
