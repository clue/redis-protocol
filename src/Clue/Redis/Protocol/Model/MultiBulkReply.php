<?php

namespace Clue\Redis\Protocol\Model;

use InvalidArgumentException;

class MultiBulkReply implements ModelInterface
{
    /**
     * @var ModelInterface[]|null
     */
    private $models;


    /**
     * create multi bulk reply (an array of other replies, usually bulk replies)
     *
     * @param ModelInterface[]|null $models
     * @throws InvalidArgumentException
     */
    public function __construct(array $models = null)
    {
        if ($models !== null) {
            foreach ($models as  $one) {
                if (!($one instanceof ModelInterface)) {
                    throw new InvalidArgumentException();
                }
            }
        }
        $this->models = $models;
    }

    public function getValueNative()
    {
        if ($this->models === null) {
            return null;
        }

        $ret = array();
        foreach ($this->models as $one) {
            /* @var $one ModelInterface */
            $ret []= $one->getValueNative();
        }
        return $ret;
    }

    public function getMessageSerialized()
    {
        if ($this->models === null) {
            /* null multi bulk reply */
            return '*-1' . self::CRLF;
        }
        /* multi bulk reply */
        $ret = '*' . count($this->models) . self::CRLF;
        foreach ($this->models as $one) {
            /* @var $one ModelInterface */
            $ret .= $one->getMessageSerialized();
        }
        return $ret;
    }

    /**
     * Checks whether this model represents a valid unified request protocol message
     *
     * The new unified protocol was introduced in Redis 1.2, but it became the
     * standard way for talking with the Redis server in Redis 2.0. The unified
     * request protocol is what Redis already uses in replies in order to send
     * list of items to clients, and is called a Multi Bulk Reply.
     *
     * @return boolean
     * @link http://redis.io/topics/protocol
     */
    public function isRequest()
    {
        if (!$this->models) {
            return false;
        }

        foreach ($this->models as $one) {
            /* @var $one ModelInterface */
            if (!($one instanceof BulkReply)) {
                return false;
            }
        }

        return true;
    }
}
