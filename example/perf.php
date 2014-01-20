<?php

use Clue\Redis\Protocol\ProtocolBuffer;
use Clue\Redis\Protocol\Factory;

require __DIR__ . '/../vendor/autoload.php';

$factory = new Factory();
$parser = $factory->createParser();
$serializer = $factory->createSerializer();

$n = isset($argv[1]) ? (int)$argv[1] : 10000; // number of dummy messages to parse
$cs = 4096; // pretend we can only read 7 bytes at once. more like 4096/8192 usually

$stream = '';
for ($i = 0; $i < $n; ++$i) {
    $stream .= $serializer->createRequestMessage(array('set', 'var' . $i, 'value' . $i));
}

$time = microtime(true);

for ($i = 0, $l = strlen($stream); $i < $l; $i += $cs) {
    $parser->pushIncoming(substr($stream, $i, $cs));

    if ($parser->hasIncoming()) {
        $parser->popIncoming();
    }
}

echo round(microtime(true) - $time, 3) . 's' . PHP_EOL;
