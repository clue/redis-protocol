<?php

namespace Clue\Redis\Protocol\Serializer;

interface Serializer
{
    public function createRequestMessage(array $args);

    /**
     *
     * @param array|string|ErrorReplyException|Status $data
     * @return string
     */
    public function createReplyMessage($data);

    public function createIntegerReply($data);
    public function createBulkReply($data);

    public function createMultiBulkReply($data);

    public function createStatusReply($message);

    public function createErrorReply($message);
}
