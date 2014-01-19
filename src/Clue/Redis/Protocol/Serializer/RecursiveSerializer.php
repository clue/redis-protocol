<?php

namespace Clue\Redis\Protocol\Serializer;

use Clue\Redis\Protocol\Model\Status;
use InvalidArgumentException;
use Exception;

class RecursiveSerializer implements SerializerInterface
{
    const CRLF = "\r\n";

    public function createRequestMessage(array $args)
    {
        return $this->createMultiBulkReply(array_map('strval', $args));
    }

    public function createReplyMessage($data)
    {
        if (is_string($data) || $data === null) {
            return $this->createBulkReply($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return $this->createIntegerReply($data);
        } else if ($data instanceof Exception) {
            return $this->createErrorReply($data);
        } else if ($data instanceof Status) {
            return $this->createStatusReply($data);
        } else if (is_array($data)) {
            return $this->createMultiBulkReply($data);
        } else {
            throw new InvalidArgumentException('Invalid data type passed for serialization');
        }
    }

    public function createIntegerReply($data)
    {
        return ':' . (int)$data . self::CRLF;
    }

    public function createBulkReply($data)
    {
        if ($data === null) {
            /* null bulk reply */
            return '$-1' . self::CRLF;
        }
        /* bulk reply */
        return '$' . strlen($data) . self::CRLF . $data . self::CRLF;
    }

    public function createMultiBulkReply($data)
    {
        if ($data === null) {
            /* null multi bulk reply */
            return '*-1' . self::CRLF;
        }
        /* multi bulk reply */
        $ret = '*' . count($data) . self::CRLF;
        foreach ($data as $one) {
            $ret .= $this->createReplyMessage($one);
        }
        return $ret;
    }

    public function createStatusReply($message)
    {
        /* status reply */
        return '+' . $message . self::CRLF;
    }

    public function createErrorReply($message)
    {
        if ($message instanceof Exception) {
            $message = $message->getMessage();
        }
        /* error status reply */
        return '-' . $message . self::CRLF;
    }
}
