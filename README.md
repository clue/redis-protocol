# redis-protocol

A Redis protocol parser / serializer written in PHP 

## Introduction

## Usage

## Install

It's very unlikely you'll want to use this protocol parser standalone. It should
be added as a dependency to your redis client implementation by adding it to
your composer.json:

```JSON
{
    "require": {
        "clue/redis-protocol": "dev-master"
    }
}
```

## License

Its parser and serializer is entirely based on
[jpd/redisent](https://github.com/jdp/redisent), which is released under the ISC
license, copyright (c) 2009-2012 Justin Poliey <justin@getglue.com>.

Other than that, this library is MIT licensed.

