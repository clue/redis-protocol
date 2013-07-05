<?php

namespace Clue\Redis\Protocol;

use Clue\Redis\Protocol\ProtocolInterface;

class ProtocolExtPhpiredis implements ProtocolInterface
{
    private $reader;

    public function __construct()
    {
        $this->reader = phpiredis_reader_create();

        // phpiredis_reader_set_error_handler($reader, $this->getErrorHandler());
    }

    public function pushIncoming($dataChunk)
    {
        phpiredis_reader_feed($this->reader, $dataChunk);
    }

    public function popIncoming()
    {
        return phpiredis_reader_get_reply($this->reader);
    }

    public function hasIncoming()
    {
        return (phpiredis_reader_get_state($this->reader) === PHPIREDIS_READER_STATE_COMPLETE);
    }

    public function createMessage(array $parts)
    {
        return phpiredis_format_command($parts);
    }
}
