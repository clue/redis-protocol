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

    public function getSerialized()
    {
        if ($this->models === null) {
            /* null multi bulk reply */
            return '*-1' . self::CRLF;
        }
        /* multi bulk reply */
        $ret = '*' . count($this->models) . self::CRLF;
        foreach ($this->models as $one) {
            /* @var $one ModelInterface */
            $ret .= $one->getSerialized();
        }
        return $ret;
    }
}
