<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\Redis\Protocol;

$factory = new Protocol\Factory();
$parser = $factory->createResponseParser();
$serializer = $factory->createSerializer();

$fp = fsockopen('tcp://localhost', 6379);
fwrite($fp, $serializer->getRequestMessage('SET', array('name', 'value')));
fwrite($fp, $serializer->getRequestMessage('GET', array('name')));

// the commands are pipelined, so this may parse multiple responses
$models = $parser->pushIncoming(fread($fp, 4096));

$reply1 = array_shift($models);
$reply2 = array_shift($models);

var_dump($reply1->getValueNative()); // string(2) "OK"
var_dump($reply2->getValueNative()); // string(5) "value"
