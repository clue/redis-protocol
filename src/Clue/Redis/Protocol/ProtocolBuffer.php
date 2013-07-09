<?php

namespace Clue\Redis\Protocol;

use Clue\Redis\Protocol\ProtocolInterface;
use Clue\Redis\Protocol\ErrorReplyException;
use UnderflowException;
use Exception;

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
    private $incomingOffset = 0;
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
        do {
            try {
                $message = $this->readResponse();
            }
            catch (Exception $e) {
                // restore previous position for next parsing attempt
                $this->incomingOffset = 0;
                break;
            }

            $this->incomingQueue []= $message;

            $this->incomingBuffer = (string)substr($this->incomingBuffer, $this->incomingOffset);
            $this->incomingOffset = 0;
        } while($this->incomingBuffer !== '');
    }

    private function readLine()
    {
        $pos = strpos($this->incomingBuffer, "\r\n", $this->incomingOffset);

        if ($pos === false) {
            throw new Exception('Unable to find CRLF sequence');
        }

        $ret = (string)substr($this->incomingBuffer, $this->incomingOffset, $pos - $this->incomingOffset);
        $this->incomingOffset = $pos + 2;

        return $ret;
    }

    private function readLength($len)
    {
        $ret = substr($this->incomingBuffer, $this->incomingOffset, $len);
        if (strlen($ret) !== $len) {
            throw new Exception('Unable to read requested number of bytes');
        }

        $this->incomingOffset += $len;

        return $ret;
    }

    /**
     * try to parse response from incoming buffer
     *
     * ripped from jdp/redisent, with some minor modifications to read from
     * the incoming buffer instead of issuing a blocking fread on a stream
     *
     * @throws Exception
     * @return mixed
     * @link https://github.com/jdp/redisent
     */
    private function readResponse() {
        /* Parse the response based on the reply identifier */
        $reply = trim($this->readLine());
        switch (substr($reply, 0, 1)) {
            /* Error reply */
            case '-':
                return new ErrorReplyException(trim(substr($reply, 1)));
                break;
                /* Inline reply */
            case '+':
                $response = substr(trim($reply), 1);
                if ($response === 'OK') {
                    $response = TRUE;
                }
                break;
                /* Bulk reply */
            case '$':
                $size = intval(substr($reply, 1));
                if ($size === -1) {
                    return null;
                }
                $response = $this->readLength($size);
                $this->readLength(2); /* discard crlf */
                break;
                /* Multi-bulk reply */
            case '*':
                $count = intval(substr($reply, 1));
                if ($count == '-1') {
                    return NULL;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $response[] = $this->readResponse();
                }
                break;
                /* Integer reply */
            case ':':
                $response = intval(substr(trim($reply), 1));
                break;
            default:
                throw new Exception("Unknown response: {$reply}");
                break;
        }
        /* Party on */
        return $response;
    }
}
