<?php

namespace Clue\Redis\Protocol\Model;

/**
 *
 * @link http://redis.io/topics/protocol#status-reply
 */
class StatusReply implements ModelInterface
{
    private $message;

    /**
     * create status reply (single line message)
     *
     * @param string|Status $message
     * @return string
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getValueNative()
    {
        return $this->message;
    }

    public function getSerialized()
    {
        /* status reply */
        return '+' . $this->message . self::CRLF;
    }
}
