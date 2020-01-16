<?php

namespace CG\Tests\Generator;

use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class PhpMethodTest extends TestCase
{
    public function testSetIsFinal(): void
    {
        $method = new PhpMethod();

        $this->assertFalse($method->isFinal());
        $this->assertSame($method, $method->setFinal(true));
        $this->assertTrue($method->isFinal());
        $this->assertSame($method, $method->setFinal(false));
        $this->assertFalse($method->isFinal());
    }

    public function testSetIsAbstract(): void
    {
        $method = new PhpMethod();

        $this->assertFalse($method->isAbstract());
        $this->assertSame($method, $method->setAbstract(true));
        $this->assertTrue($method->isAbstract());
        $this->assertSame($method, $method->setAbstract(false));
        $this->assertFalse($method->isAbstract());
    }

    public function testSetGetParameters(): void
    {
        $method = new PhpMethod();

        $this->assertEquals([], $method->getParameters());
        $this->assertSame($method, $method->setParameters($params = [new PhpParameter()]));
        $this->assertSame($params, $method->getParameters());

        $this->assertSame($method, $method->addParameter($param = new PhpParameter()));
        $params[] = $param;
        $this->assertSame($params, $method->getParameters());

        $this->assertSame($method, $method->removeParameter(0));
        unset($params[0]);
        $this->assertSame([$param], $method->getParameters());

        $this->assertSame($method, $method->addParameter($param = new PhpParameter()));
        $params[] = $param;
        $params = array_values($params);
        $this->assertSame($params, $method->getParameters());
    }

    public function testSetGetBody(): void
    {
        $method = new PhpMethod();

        $this->assertSame('', $method->getBody());
        $this->assertSame($method, $method->setBody('foo'));
        $this->assertEquals('foo', $method->getBody());
    }

    public function testSetIsReferenceReturned(): void
    {
        $method = new PhpMethod();

        $this->assertFalse($method->isReferenceReturned());
        $this->assertSame($method, $method->setReferenceReturned(true));
        $this->assertTrue($method->isReferenceReturned());
        $this->assertSame($method, $method->setReferenceReturned(false));
        $this->assertFalse($method->isReferenceReturned());
    }

    /**
     * @throws ReflectionException
     */
    public function testFromReflectionReturnTypeAllowsNull(): void
    {
        $reflectionClass = new ReflectionClass(FooBar::class);
        $method = PhpMethod::fromReflection($reflectionClass->getMethod('foo'));
        $this->assertTrue($method->isNullAllowedForReturnType());
    }
}

class FooBar
{
    public function foo(): ?bool
    {
        return null;
    }
}
