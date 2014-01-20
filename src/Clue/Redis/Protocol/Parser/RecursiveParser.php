<?php

namespace Clue\Redis\Protocol\Parser;

use Clue\Redis\Protocol\Parser\ParserInterface;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Model\BulkReply;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Model\IntegerReply;
use Clue\Redis\Protocol\Model\MultiBulkReply;
use Clue\Redis\Protocol\Model\StatusReply;
use Clue\Redis\Protocol\Parser\ParserException;
use UnderflowException;
use Exception;

/**
 * Simple recursive redis wire protocol parser
 *
 * Heavily influenced by blocking parser implementation from jpd/redisent.
 *
 * @link https://github.com/jdp/redisent
 * @link http://redis.io/topics/protocol
 */
class RecursiveParser implements ParserInterface
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
        if (!$this->incomingQueue) {
            throw new UnderflowException('Incoming message queue is empty');
        }
        return array_shift($this->incomingQueue);
    }

    public function hasIncoming()
    {
        return ($this->incomingQueue) ? true : false;
    }

    private function tryParsingIncomingMessages()
    {
        do {
            try {
                $message = $this->readResponse();
            }
            catch (UnderflowException $e) {
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
            throw new UnderflowException('Unable to find CRLF sequence');
        }

        $ret = (string)substr($this->incomingBuffer, $this->incomingOffset, $pos - $this->incomingOffset);
        $this->incomingOffset = $pos + 2;

        return $ret;
    }

    private function readLength($len)
    {
        $ret = substr($this->incomingBuffer, $this->incomingOffset, $len);
        if (strlen($ret) !== $len) {
            throw new UnderflowException('Unable to read requested number of bytes');
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
     * @throws UnderflowException if the incoming buffer is incomplete
     * @throws ParserException if the incoming buffer is invalid
     * @return ModelInterface
     * @link https://github.com/jdp/redisent
     */
    private function readResponse() {
        /* Parse the response based on the reply identifier */
        $reply = trim($this->readLine());
        switch (substr($reply, 0, 1)) {
            /* Error reply */
            case '-':
                $response = new ErrorReply(trim(substr($reply, 1)));
                break;
                /* Inline reply */
            case '+':
                $response = new StatusReply(substr(trim($reply), 1));
                break;
                /* Bulk reply */
            case '$':
                $size = intval(substr($reply, 1));
                if ($size === -1) {
                    return new BulkReply(null);
                }
                $response = new BulkReply($this->readLength($size));
                $this->readLength(2); /* discard crlf */
                break;
                /* Multi-bulk reply */
            case '*':
                $count = intval(substr($reply, 1));
                if ($count == '-1') {
                    return new MultiBulkReply(null);
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $response[] = $this->readResponse();
                }
                $response = new MultiBulkReply($response);
                break;
                /* Integer reply */
            case ':':
                $response = new IntegerReply(substr(trim($reply), 1));
                break;
            default:
                throw new ParserException("Unknown response: {$reply}");
                break;
        }
        /* Party on */
        return $response;
    }
}
