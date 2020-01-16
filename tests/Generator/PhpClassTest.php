<?php

namespace CG\Tests\Generator;

use CG\Generator\PhpClass;
use CG\Generator\PhpConstant;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use CG\Tests\Generator\Fixture\Entity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class PhpClassTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testFromReflection(): void
    {
        $class = new PhpClass();
        $class
            ->setName(Entity::class)
            ->setAbstract(true)
            ->setDocblock('/**
 * Doc Comment.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */')
            ->setProperty(PhpProperty::create('id')
                ->setVisibility('private')
                ->setDocblock('/**
 * @var integer
 */')
            )
            ->setProperty(PhpProperty::create('enabled')
                ->setVisibility('private')
                ->setDefaultValue(false)
            );

        $method = PhpMethod::create()
            ->setName('__construct')
            ->setFinal(true)
            ->addParameter(new PhpParameter('a'))
            ->addParameter(PhpParameter::create()
                ->setName('b')
                ->setType('array')
                ->setPassedByReference(true)
            )
            ->addParameter(PhpParameter::create()
                ->setName('c')
                ->setType('stdClass')
            )
            ->addParameter(PhpParameter::create()
                ->setName('d')
                ->setDefaultValue('foo')
            )->setDocblock('/**
 * Another doc comment.
 *
 * @param unknown_type $a
 * @param array        $b
 * @param \stdClass    $c
 * @param string       $d
 */');
        $class->setMethod($method);

        $class->setMethod(PhpMethod::create()
            ->setName('foo')
            ->setAbstract(true)
            ->setVisibility('protected')
        );

        $class->setMethod(PhpMethod::create()
            ->setName('bar')
            ->setStatic(true)
            ->setVisibility('private')
        );

        $this->assertEquals($class, PhpClass::fromReflection(new ReflectionClass(Entity::class)));
    }

    public function testGetSetName(): void
    {
        $class = new PhpClass();
        $this->assertNull($class->getName());

        $class = new PhpClass('foo');
        $this->assertEquals('foo', $class->getName());
        $this->assertSame($class, $class->setName('bar'));
        $this->assertEquals('bar', $class->getName());
    }

    public function testSetGetConstants(): void
    {
        $class = new PhpClass();

        $this->assertEquals([], $class->getConstants());
        $this->assertSame($class, $class->setConstants(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $class->getConstants());
        $this->assertSame($class, $class->setConstant('bar', 'baz'));
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $class->getConstants());
        $this->assertSame($class, $class->setConstant(PhpConstant::create('foo')->setValue('baz')));
        $this->assertEquals(['foo' => 'baz', 'bar' => 'baz'], $class->getConstants());
        $this->assertSame($class, $class->removeConstant('foo'));
        $this->assertEquals(['bar' => 'baz'], $class->getConstants());
        $this->assertTrue($class->hasConstant('bar'));
        $this->assertFalse($class->hasConstant('foo'));

        $phpConstant = $class->getConstant('bar');
        $this->assertEquals('bar', $phpConstant->getName());
        $this->assertEquals('baz', $phpConstant->getValue());

        $this->expectException(InvalidArgumentException::class);
        $class->setConstant(PhpConstant::create('foo'), 'baz');
    }

    public function testGetConstantThrowsExceptionWhenConstantDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $class = new PhpClass();
        $class->getConstant('foo');
    }

    public function testRemoveConstantThrowsExceptionWhenConstantDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $class = new PhpClass();
        $class->removeConstant('foo');
    }

    public function testSetIsAbstract(): void
    {
        $class = new PhpClass();

        $this->assertFalse($class->isAbstract());
        $this->assertSame($class, $class->setAbstract(true));
        $this->assertTrue($class->isAbstract());
        $this->assertSame($class, $class->setAbstract(false));
        $this->assertFalse($class->isAbstract());
    }

    public function testSetIsFinal(): void
    {
        $class = new PhpClass();

        $this->assertFalse($class->isFinal());
        $this->assertSame($class, $class->setFinal(true));
        $this->assertTrue($class->isFinal());
        $this->assertSame($class, $class->setFinal(false));
        $this->assertFalse($class->isFinal());
    }

    public function testSetGetParentClassName(): void
    {
        $class = new PhpClass();

        $this->assertNull($class->getParentClassName());
        $this->assertSame($class, $class->setParentClassName('stdClass'));
        $this->assertEquals('stdClass', $class->getParentClassName());
        $this->assertSame($class, $class->setParentClassName(null));
        $this->assertNull($class->getParentClassName());
    }

    public function testSetGetInterfaceNames(): void
    {
        $class = new PhpClass();

        $this->assertEquals([], $class->getInterfaceNames());
        $this->assertSame($class, $class->setInterfaceNames(['foo', 'bar']));
        $this->assertEquals(['foo', 'bar'], $class->getInterfaceNames());
        $this->assertSame($class, $class->addInterfaceName('stdClass'));
        $this->assertEquals(['foo', 'bar', 'stdClass'], $class->getInterfaceNames());
    }

    public function testSetGetUseStatements(): void
    {
        $class = new PhpClass();

        $this->assertEquals([], $class->getUseStatements());
        $this->assertSame($class, $class->setUseStatements(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $class->getUseStatements());
        $this->assertSame($class, $class->addUseStatement('Foo\Bar'));
        $this->assertEquals(['foo' => 'bar', 'Bar' => 'Foo\Bar'], $class->getUseStatements());
        $this->assertSame($class, $class->addUseStatement('Foo\Bar', 'Baz'));
        $this->assertEquals(['foo' => 'bar', 'Bar' => 'Foo\Bar', 'Baz' => 'Foo\Bar'], $class->getUseStatements());
        $this->assertSame($class, $class->setUseStatements(['Bar\Baz']));
        $this->assertEquals(['foo' => 'bar', 'Bar' => 'Foo\Bar', 'Baz' => 'Bar\Baz'], $class->getUseStatements());
    }

    public function testUsesClassWithoutUseStatements(): void
    {
        $class = new PhpClass();
        $this->assertFalse($class->uses('DateTime'));
    }

    /**
     * @dataProvider usesClassDataProvider
     * @param string $usage
     * @param string $typedef
     * @param bool $expected
     */
    public function testUsesClass(string $usage, string $typedef, bool $expected): void
    {
        $class = new PhpClass();
        $class->addUseStatement($usage);
        $this->assertEquals($expected, $class->uses($typedef));
    }

    public function usesClassDataProvider(): array
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        return [
            ['\DateTime', '\DateTime', false], // using fqdn from root ignores use statements
            ['\DateTime', 'DateTime', true],
            ['Foo\Bar\Baz', 'Baz', true],
            ['Foo\Bar\Baz', 'Bar', false],
            ['Foo\Bar\Baz', 'Foo', false],
            ['Foo\Bar', 'Bar\Baz', true],
            ['Foo\Bar', '\Bar\Baz', false]
        ];
    }


    public function emptyTypeDefDataProvider(): array
    {
        return [
            [''],
            [false],
            [null],
        ];
    }

    /**
     * @param string $typeDef
     * @dataProvider emptyTypeDefDataProvider
     */
    public function testUsesShouldThrowExceptionIfTypeDefIsEmpty($typeDef): void
    {
        $class = new PhpClass();
        $this->expectException(InvalidArgumentException::class);
        $class->uses($typeDef);
    }

    public function testSetGetProperties(): void
    {
        $class = new PhpClass();

        $this->assertEquals([], $class->getProperties());
        $this->assertSame($class, $class->setProperties($props = ['foo' => new PhpProperty()]));
        $this->assertSame($props, $class->getProperties());
        $this->assertSame($class, $class->setProperty($prop = new PhpProperty('foo')));
        $this->assertSame(['foo' => $prop], $class->getProperties());
        $this->assertTrue($class->hasProperty('foo'));
        $this->assertSame($class, $class->removeProperty('foo'));
        $this->assertEquals([], $class->getProperties());

        $this->expectException(InvalidArgumentException::class);
        $class->removeProperty('foo');
    }

    public function testSetGetMethods(): void
    {
        $class = new PhpClass();

        $this->assertEquals([], $class->getMethods());
        $phpMethod = new PhpMethod();
        $this->assertSame($class, $class->setMethods($methods = ['foo' => $phpMethod]));
        $this->assertSame($methods, $class->getMethods());
        $this->assertEquals($phpMethod, $class->getMethod('foo'));
        $this->assertSame($class, $class->setMethod($method = new PhpMethod('foo')));
        $this->assertSame(['foo' => $method], $class->getMethods());
        $this->assertTrue($class->hasMethod('foo'));
        $this->assertSame($class, $class->removeMethod('foo'));
        $this->assertEquals([], $class->getMethods());

        $this->expectException(InvalidArgumentException::class);
        $class->getMethod('foo');
    }

    public function testRemoveMethodShouldThrowExceptionWhenMethodDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $class = new PhpClass();
        $class->removeMethod('foo');
    }

    public function testSetGetDocblock(): void
    {
        $class = new PhpClass();

        $this->assertNull($class->getDocblock());
        $this->assertSame($class, $class->setDocblock('foo'));
        $this->assertEquals('foo', $class->getDocblock());
    }

    public function testSetGetRequiredFiles(): void
    {
        $class = new PhpClass();

        $this->assertEquals([], $class->getRequiredFiles());
        $this->assertSame($class, $class->setRequiredFiles(['foo']));
        $this->assertEquals(['foo'], $class->getRequiredFiles());
        $this->assertSame($class, $class->addRequiredFile('bar'));
        $this->assertEquals(['foo', 'bar'], $class->getRequiredFiles());
    }
}
