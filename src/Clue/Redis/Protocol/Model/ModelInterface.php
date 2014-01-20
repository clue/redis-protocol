<?php

namespace Clue\Redis\Protocol\Model;

interface ModelInterface
{
    const CRLF = "\r\n";

    public function getValueNative();

    public function getMessageSerialized();
}
