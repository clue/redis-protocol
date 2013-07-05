<?php

use Clue\Redis\Protocol\ProtocolExtPhpiredis;

class ProtocolExtPhpiredisTest extends ProtocolBaseTest
{
    protected function createProtocol()
    {
        if (!function_exists('phpiredis_reader_create')) {
            return $this->markTestSkipped();
        }
        return new ProtocolExtPhpiredis();
    }
}
