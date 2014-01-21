# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

## 0.2.0 (2014-01-21)

* Re-organize the whole API into dedicated
  * `Parser` (protocol reader) and
  * `Serializer` (protocol writer) sub-namespaces. (#4)

* Use of the factory has now been unified:

  ```php
  $factory = new Clue\Redis\Protocol\Factory();
  $parser = $factory->createParser();
  $serializer = $factory->createSerializer();
  ```

* Add a dedicated `Model` for each type of reply. Among others, this now allows
  you to distinguish a single line `StatusReply` from a binary-safe `BulkReply`. (#2)

* Fix parsing binary values and do not trip over trailing/leading whitespace. (#4)

* Improve parser and serializer performance by up to 20%. (#4)

## 0.1.0 (2013-09-10)

* First tagged release

