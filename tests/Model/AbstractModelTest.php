<?php

abstract class AbstractModelTest extends TestCase
{
    abstract protected function createModel($value);

    public function testConstructor()
    {
        $model = $this->createModel(null);

        $this->assertInstanceOf('Clue\Redis\Protocol\Model\ModelInterface', $model);
    }
}
