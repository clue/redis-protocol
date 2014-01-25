<?php

namespace Clue\Redis\Protocol\Serializer;

use Clue\Redis\Protocol\Model\ErrorReplyException;
use Clue\Redis\Protocol\Model\ModelInterface;
use Clue\Redis\Protocol\Model\MultiBulkReply;

interface SerializerInterface
{
    /**
     * create a serialized unified request protocol message
     *
     * This is the *one* method most redis client libraries will likely want to
     * use in order to send a serialized message (a request) over the* wire to
     * your redis server instance.
     *
     * This method should be used in favor of constructing a request model and
     * then serializing it. While its effect might be equivalent, this method
     * is likely to (i.e. it /could/) provide a faster implementation.
     *
     * @param array $args
     * @return string
     * @see self::createRequestMessage()
     */
    public function getRequestMessage(array $args);

    /**
     * create a unified request protocol message model
     *
     * @param array $args
     * @return MultiBulkReply
     */
    public function createRequestModel(array $args);

    /**
     * create response message by determining datatype from given argument
     *
     * @param mixed $data
     * @return ModelInterface
     */
    public function createReplyModel($data);
}
