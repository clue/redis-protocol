<?php

use Clue\Redis\Protocol\Serializer\RecursiveSerializer;

abstract class AbstractModelTest extends TestCase
{
    protected $serializer;

    abstract protected function createModel($value);

    public function setUp()
    {
        $this->serializer = new RecursiveSerializer();
    }

    public function testConstructor()
    {
        $model = $this->createModel(null);

        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ModelInterface', $model);
    }
}
