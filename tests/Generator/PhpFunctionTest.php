<?php

namespace CG\Tests\Generator;

use CG\Generator\PhpFunction;
use CG\Generator\PhpParameter;
use PHPUnit\Framework\TestCase;
use foo\bar;

class PhpFunctionTest extends TestCase
{
    public function testSetGetName(): void
    {
        $func = new PhpFunction();

        $this->assertNull($func->getName());
        $this->assertSame($func, $func->setName('foo'));
        $this->assertEquals('foo', $func->getName());

        $func = new PhpFunction('foo');
        $this->assertEquals('foo', $func->getName());
    }

    public function testSetGetQualifiedName(): void
    {
        $func = new PhpFunction();

        $this->assertSame($func, $func->setQualifiedName(bar::class));
        $this->assertEquals('foo', $func->getNamespace());
        $this->assertEquals('bar', $func->getName());
        $this->assertEquals(bar::class, $func->getQualifiedName());

        $this->assertSame($func, $func->setQualifiedName('foo'));
        $this->assertNull($func->getNamespace());
        $this->assertEquals('foo', $func->getName());
        $this->assertEquals('foo', $func->getQualifiedName());
    }

    public function testSetGetNamespace(): void
    {
        $func = new PhpFunction();

        $this->assertNull($func->getNamespace());
        $this->assertSame($func, $func->setNamespace('foo'));
        $this->assertEquals('foo', $func->getNamespace());
    }

    public function testSetGetBody(): void
    {
        $func = new PhpFunction();

        $this->assertSame('', $func->getBody());
        $this->assertSame($func, $func->setBody('foo'));
        $this->assertEquals('foo', $func->getBody());
    }

    public function testSetGetParameters(): void
    {
        $func = new PhpFunction();

        $this->assertEquals([], $func->getParameters());
        $this->assertSame($func, $func->setParameters([$param = new PhpParameter()]));
        $this->assertSame([$param], $func->getParameters());
        $this->assertSame($func, $func->addParameter($param2 = new PhpParameter()));
        $this->assertSame([$param, $param2], $func->getParameters());
        $this->assertSame($func, $func->replaceParameter(1, $param3 = new PhpParameter()));
        $this->assertSame([$param, $param3], $func->getParameters());
        $this->assertSame($func, $func->removeParameter(0));
        $this->assertSame([$param3], $func->getParameters());
    }

    public function testSetGetDocblock(): void
    {
        $func = new PhpFunction();

        $this->assertNull($func->getDocblock());
        $this->assertSame($func, $func->setDocblock('foo'));
        $this->assertEquals('foo', $func->getDocblock());
    }

    public function testSetIsReferenceReturned(): void
    {
        $func = new PhpFunction();

        $this->assertFalse($func->isReferenceReturned());
        $this->assertSame($func, $func->setReferenceReturned(true));
        $this->assertTrue($func->isReferenceReturned());
        $this->assertSame($func, $func->setReferenceReturned(false));
        $this->assertFalse($func->isReferenceReturned());
    }
}