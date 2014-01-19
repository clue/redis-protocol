<?php

namespace Clue\Redis\Protocol\Serializer;

class RecursiveSerializer implements SerializerInterface
{
    const CRLF = "\r\n";

    public function createRequestMessage(array $args)
    {
        return $this->createMultiBulkReply(array_map('strval', $args));

        return sprintf('*%d%s%s%s', count($args), ProtocolBuffer::CRLF, implode(array_map(function($arg) {
            return sprintf('$%d%s%s', strlen($arg), ProtocolBuffer::CRLF, $arg);
        }, $args), ProtocolBuffer::CRLF), ProtocolBuffer::CRLF);
    }

    /**
     *
     * @param array|string|ErrorReplyException|Status $data
     * @return string
     */
    public function createReplyMessage($data)
    {
        if (is_string($data) || $data === null) {
            return $this->createBulkReply($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return $this->createIntegerReply($data);
        } else if ($data instanceof ErrorReplyException) {
            return $this->createErrorReply($data->getmessage());
        } else if ($data instanceof Status) {
            return $this->createStatusReply($data->getMessage());
        } else if (is_array($data)) {
            return $this->createMultiBulkReply($data);
        } else {
            throw new ParserException('Invalid data type passed for serialization');
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
        return sprintf('$%d%s%s%s', strlen($data), self::CRLF, $data, self::CRLF);
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
        /* error status reply */
        return '-' . $message . self::CRLF;
    }
}
