<?php

namespace CG\Tests\Generator;

use CG\Generator\PhpConstant;
use PHPUnit\Framework\TestCase;

class PhpConstantTest extends TestCase
{

    public function testSetGet()
    {
        $constant = PhpConstant::create();
        $this->assertNull($constant->getName());
        $this->assertNull($constant->getValue());
        $constant = PhpConstant::create('foo');
        $this->assertEquals('foo', $constant->getName());
        $this->assertNull($constant->getValue());
        $constant->setName('bar');
        $this->assertEquals('bar', $constant->getName());
        $this->assertNull($constant->getValue());
        $constant->setValue('baz');
        $this->assertEquals('bar', $constant->getName());
        $this->assertEquals('baz', $constant->getValue());
    }
}
