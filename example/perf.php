<?php

use Clue\Redis\Protocol\ProtocolBuffer;

require __DIR__ . '/../vendor/autoload.php';

$parser = new ProtocolBuffer();

$n = isset($argv[1]) ? (int)$argv[1] : 1000; // number of dummy messages to parse
$cs = 7; // pretend we can only read 7 bytes at once. more like 4096/8192 usually

$stream = '';
for ($i = 0; $i < $n; ++$i) {
    $stream .= $parser->createMessage(array('set', 'var' . $i, 'value' . $i));
}

$time = microtime(true);

for ($i = 0, $l = strlen($stream); $i < $l; $i += $cs) {
    $parser->pushIncoming(substr($stream, $i, $cs));

    if ($parser->hasIncoming()) {
        $parser->popIncoming();
    }
}

echo round(microtime(true) - $time, 3) . 's' . PHP_EOL;
