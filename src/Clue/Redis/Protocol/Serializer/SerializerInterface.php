<?php

namespace Clue\Redis\Protocol\Serializer;

use Clue\Redis\Protocol\Model\ErrorReplyException;

interface SerializerInterface
{
    /**
     * create unified request protocol message
     *
     * @param array $args
     * @return string
     */
    public function createRequestMessage(array $args);

    /**
     * create response message by determining datatype from given argument
     *
     * @param mixed $data
     * @return string
     */
    public function createReplyMessage($data);

    /**
     * create integer reply
     *
     * @param int $data
     * @return string
     */
    public function createIntegerReply($data);

    /**
     * create bulk reply (string reply)
     *
     * @param string|null $data
     * @return string
     */
    public function createBulkReply($data);

    /**
     * create multi bulk reply (an array of other replies, usually strings)
     *
     * @param array|null $data
     */
    public function createMultiBulkReply($data);

    /**
     * create status reply (single line message)
     *
     * @param string|Status $message
     * @return string
     */
    public function createStatusReply($message);

    /**
     * create error status reply (single line error message)
     *
     * @param string|ErrorReplyException $message
     * @return string
     */
    public function createErrorReply($message);
}
