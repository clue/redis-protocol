<?php

namespace Clue\Redis\Protocol\Model;

use Clue\Redis\Protocol\Model\ModelInterface;

class BulkReply implements ModelInterface
{
    private $value;

    /**
     * create bulk reply (string reply)
     *
     * @param string|null $data
     */
    public function __construct($value)
    {
        if ($value !== null) {
            $value = (string)$value;
        }
        $this->value = $value;
    }

    public function getValueNative()
    {
        return $this->value;
    }

    public function getSerialized()
    {
        if ($this->value === null) {
            /* null bulk reply */
            return '$-1' . self::CRLF;
        }
        /* bulk reply */
        return '$' . strlen($this->value) . self::CRLF . $this->value . self::CRLF;
    }
}
