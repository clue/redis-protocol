<?php

namespace Clue\Redis\Protocol\Model;

/**
 *
 * @link http://redis.io/topics/protocol#status-reply
 */
class Status
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        return $this->message;
    }
}
