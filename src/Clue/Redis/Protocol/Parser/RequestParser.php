<?php

namespace Clue\Redis\Protocol\Parser;

use Clue\Redis\Protocol\Parser\ParserException;
use Clue\Redis\Protocol\Model\Request;

class RequestParser implements ParserInterface
{
    const CRLF = "\r\n";

    private $incomingBuffer = '';
    private $incomingOffset = 0;

    public function pushIncoming($dataChunk)
    {
        $this->incomingBuffer .= $dataChunk;

        return $this->tryParsingIncomingMessages();
    }

    private function tryParsingIncomingMessages()
    {
        $parsed = array();

        do {
            $message = $this->readRequest();
            if ($message === null) {
                // restore previous position for next parsing attempt
                $this->incomingOffset = 0;
                break;
            }

            if ($message !== false) {
                $parsed []= $message;
            }

            $this->incomingBuffer = (string)substr($this->incomingBuffer, $this->incomingOffset);
            $this->incomingOffset = 0;
        } while($this->incomingBuffer !== '');

        return $parsed;
    }

    private function readLine()
    {
        $pos = strpos($this->incomingBuffer, "\r\n", $this->incomingOffset);

        if ($pos === false) {
            return null;
        }

        $ret = (string)substr($this->incomingBuffer, $this->incomingOffset, $pos - $this->incomingOffset);
        $this->incomingOffset = $pos + 2;

        return $ret;
    }

    private function readLength($len)
    {
        $ret = substr($this->incomingBuffer, $this->incomingOffset, $len);
        if (strlen($ret) !== $len) {
            return null;
        }

        $this->incomingOffset += $len;

        return $ret;
    }

    /**
     * try to parse request from incoming buffer
     *
     * @throws ParserException if the incoming buffer is invalid
     * @return Request|null
     * @link https://github.com/jdp/redisent
     */
    private function readRequest() {
        $line = $this->readLine();
        if ($line === null) {
            return null;
        }

        if (isset($line[0]) && $line[0] === '*') {
            $line[0] = ' ';
            $count = (int)$line;

            if ($count <= 0) {
                return false;
            }
            $command = null;
            $args    = array();
            for ($i = 0; $i < $count; ++$i) {
                $sub = $this->readBulk();
                if ($sub === null) {
                    return null;
                }
                if ($command === null) {
                    $command = $sub;
                } else {
                    $args []= $sub;
                }
            }
            return new Request($command, $args);
        }

        $args = preg_split('/ +/', trim($line, ' '));
        $command = array_shift($args);

        if ($command === '') {
            return false;
        }

        return new Request($command, $args);
    }

    private function readBulk()
    {
        $line = $this->readLine();
        if ($line === null) {
            return null;
        }
        if (isset($line[0]) && $line[0] !== '$') {
            throw new ParserException('ERR Protocol error: expected \'$\', got \'' . substr($line, 0, 1) . '\'');
        }

        $line[0] = ' ';
        $size = (int)$line;

        if ($size < 0) {
            throw new ParserException('ERR Protocol error: invalid bulk length');
        }
        $data = $this->readLength($size);
        if ($data === null) {
            return null;
        }
        if ($this->readLength(2) === null) { /* discard crlf */
            return null;
        }
        return $data;
    }
}
