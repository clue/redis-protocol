<?php

namespace Clue\Tests\Redis\Protocol\Model;

use Clue\Redis\Protocol\Serializer\RecursiveSerializer;
use Clue\Tests\Redis\Protocol\TestCase;

abstract class AbstractModelTest extends TestCase
{
    protected $serializer;

    abstract protected function createModel($value);

    /**
     * @before
     */
    public function setUpSerializer()
    {
        $this->serializer = new RecursiveSerializer();
    }

    public function testConstructor()
    {
        $model = $this->createModel(null);

        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ModelInterface', $model);
    }
}
