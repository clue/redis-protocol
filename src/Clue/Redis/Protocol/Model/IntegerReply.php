<?php

namespace Clue\Redis\Protocol\Model;

use Clue\Redis\Protocol\Model\ModelInterface;

class IntegerReply implements ModelInterface
{
    private $value;

    /**
     * create integer reply
     *
     * @param int $data
     */
    public function __construct($value)
    {
        $this->value = (int)$value;
    }

    public function getValueNative()
    {
        return $this->value;
    }

    public function getSerialized()
    {
        return ':' . $this->value . self::CRLF;
    }
}
