<?php

namespace Clue\Redis\Protocol\Serializer;

use InvalidArgumentException;
use Exception;
use Clue\Redis\Protocol\Model\BulkReply;
use Clue\Redis\Protocol\Model\IntegerReply;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Model\MultiBulkReply;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Model\Request;

class RecursiveSerializer implements SerializerInterface
{
    const CRLF = "\r\n";

    public function getRequestMessage($command, array $args = array(), array $options = array())
    {
        $argsCount = count($args);
        $optionsCount = 0;
        $optionsValueCount = 0;
        $optionsData = '';
        foreach ($options as $option => $value) {
            if ($value) {
                $optionsCount++;
                $optionsData .= '$' . strlen($option) . self::CRLF . $option . self::CRLF;
                if (
                    !is_bool($value) &&
                    !is_array($value) &&
                    (!is_object($value) || (is_object($value) && method_exists($value, '__toString')))
                ) {
                    $optionsValueCount++;
                    $optionsData .= '$' . strlen((string)$value) . self::CRLF . $value . self::CRLF;
                }
            }
        }
        $count = 1 + $argsCount + $optionsCount + $optionsValueCount;
        $data = '*' . $count . "\r\n$" . strlen($command) . self::CRLF . $command . self::CRLF;
        foreach ($args as $arg) {
            $data .= '$' . strlen($arg) . self::CRLF . $arg . self::CRLF;
        }
        $data .= $optionsData;

        return $data;
    }

    public function createRequestModel($command, array $args = array())
    {
        return new Request($command, $args);
    }

    public function getReplyMessage($data)
    {
        if (is_string($data) || $data === null) {
            return $this->getBulkMessage($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return $this->getIntegerMessage($data);
        } else if ($data instanceof Exception) {
            return $this->getErrorMessage($data->getMessage());
        } else if (is_array($data)) {
            return $this->getMultiBulkMessage($data);
        } else {
            throw new InvalidArgumentException('Invalid data type passed for serialization');
        }
    }

    public function createReplyModel($data)
    {
        if (is_string($data) || $data === null) {
            return new BulkReply($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return new IntegerReply($data);
        } else if ($data instanceof Exception) {
            return new ErrorReply($data->getMessage());
        } else if (is_array($data)) {
            $models = array();
            foreach ($data as $one) {
                $models []= $this->createReplyModel($one);
            }
            return new MultiBulkReply($models);
        } else {
            throw new InvalidArgumentException('Invalid data type passed for serialization');
        }
    }

    public function getBulkMessage($data)
    {
        if ($data === null) {
            /* null bulk reply */
            return '$-1' . self::CRLF;
        }
        /* bulk reply */
        return '$' . strlen($data) . self::CRLF . $data . self::CRLF;
    }

    public function getErrorMessage($data)
    {
        /* error status reply */
        return '-' . $data . self::CRLF;
    }

    public function getIntegerMessage($data)
    {
        return ':' . (int)$data . self::CRLF;
    }

    public function getMultiBulkMessage($data)
    {
        if ($data === null) {
            /* null multi bulk reply */
            return '*-1' . self::CRLF;
        }
        /* multi bulk reply */
        $ret = '*' . count($data) . self::CRLF;
        foreach ($data as $one) {
            if ($one instanceof ModelInterface) {
                $ret .= $one->getMessageSerialized($this);
            } else {
                $ret .= $this->getReplyMessage($one);
            }
        }
        return $ret;
    }

    public function getStatusMessage($data)
    {
        /* status reply */
        return '+' . $data . self::CRLF;
    }
}
