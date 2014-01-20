<?php

namespace Clue\Redis\Protocol\Serializer;

use Clue\Redis\Protocol\Model\StatusReply;
use InvalidArgumentException;
use Exception;
use Clue\Redis\Protocol\Model\BulkReply;
use Clue\Redis\Protocol\Model\IntegerReply;
use Clue\Redis\Protocol\Model\ErrorReply;
use Clue\Redis\Protocol\Model\MultiBulkReply;

class RecursiveSerializer implements SerializerInterface
{
    public function createRequestMessage(array $args)
    {
        return $this->createRequestModel($args)->getSerialized();
    }

    public function createRequestModel(array $args)
    {
        $models = array();
        foreach ($args as $arg) {
            $models []= new BulkReply($arg);
        }
        return new MultiBulkReply($models);
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
}
