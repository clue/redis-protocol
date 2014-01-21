<?php

namespace Clue\Redis\Protocol\Model;

interface ModelInterface
{
    const CRLF = "\r\n";

    /**
     * Returns value of this model as a native representation for PHP
     *
     * @return mixed
     */
    public function getValueNative();

    /**
     * Returns the serialized representation of this protocol message
     *
     * @return string
     */
    public function getMessageSerialized();
}
