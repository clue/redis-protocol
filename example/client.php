<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\Redis\Protocol;

$factory = new Protocol\Factory();
$parser = $factory->createParser();
$serializer = $factory->createSerializer();

$fp = fsockopen('tcp://localhost', 6379);
fwrite($fp, $serializer->getRequestMessage('SET', array('name', 'value')));
fwrite($fp, $serializer->getRequestMessage('GET', array('name')));

// the commands are pipelined, so this may parse multiple responses
$parser->pushIncoming(fread($fp, 4096));

$reply1 = $parser->popIncomingModel();
$reply2 = $parser->popIncomingModel();

var_dump($reply1->getValueNative()); // string(2) "OK"
var_dump($reply2->getValueNative()); // string(5) "value"
