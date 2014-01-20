# redis-protocol [![Build Status](https://travis-ci.org/clue/redis-protocol.png?branch=master)](https://travis-ci.org/clue/redis-protocol)

A Redis protocol parser / serializer written in PHP 

## Introduction

This parser and serializer implementation allows you to parse redis protocol
messages into native PHP values and vice-versa. This should usually be
controlled through a redis client implementation which handles the connection
socket.

To re-iterate: This is *not* a redis client implementation. This is a protocol
implementation that is usually used by a redis client implementation. If you're
looking for an easy way to build your own client implementation, then this is
for you. If you merely want to connect to a redis server and issue some
commands, you're probably better off using one of the existing client
implementations.

## Usage

```php
use Clue\Redis\Protocol;

$factory = new Protocol\Factory();
$parser = $factory->createParser();
$serializer = $factory->createSerializer();

$fp = fsockopen('tcp://localhost', 6379);
fwrite($fp, $serializer->createRequestMessage(array('SET', 'name', 'value')));
fwrite($fp, $serializer->createRequestMessage(array('GET', 'name')));

// the commands are pipelined, so this may parse multiple responses
$parser->pushIncoming(fread($fp, 4096));

$reply1 = $parser->popIncoming();
$reply2 = $parser->popIncoming();

var_dump($reply1->getValueNative()); // (string)"OK"
var_dump($reply2->getValueNative()); // (string)"value"
```

## Install

It's very unlikely you'll want to use this protocol parser standalone. It should
be added as a dependency to your redis client implementation by adding it to
your composer.json:

```JSON
{
    "require": {
        "clue/redis-protocol": "0.1.*"
    }
}
```

## License

Its parser and serializer is entirely based on
[jpd/redisent](https://github.com/jdp/redisent), which is released under the ISC
license, copyright (c) 2009-2012 Justin Poliey <justin@getglue.com>.

Other than that, this library is MIT licensed.

