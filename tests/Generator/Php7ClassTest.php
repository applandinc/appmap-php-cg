<?php

namespace CG\Tests\Generator;

use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use CG\Tests\Generator\Fixture\EntityPhp7;
use CG\Tests\Generator\Fixture\SubFixture\Bar;
use CG\Tests\Generator\Fixture\SubFixture\Baz;
use CG\Tests\Generator\Fixture\SubFixture\Foo;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class Php7ClassTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testFromReflection(): void
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Test is only valid for PHP >=7');
        }
        $class = new PhpClass(EntityPhp7::class);
        $class
            ->setDocblock('/**
 * Doc Comment.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */')
            ->setProperty(PhpProperty::create('id')
                ->setVisibility('private')
                ->setDefaultValue(0)
                ->setDocblock('/**
 * @var integer
 */')
            );

        $class->setMethod(PhpMethod::create('getId')
            ->setDocblock('/**
 * @return int
 */')
            ->setVisibility('public')
            ->setReturnType('int')
        );

        $class->setMethod(PhpMethod::create('setId')
            ->setVisibility('public')
            ->setDocBlock('/**
 * @param int $id
 * @return EntityPhp7
 */')
            ->addParameter(PhpParameter::create('id')
                ->setType('int')
                ->setDefaultValue(null)
            )
            ->setReturnType('self')
        );

        $class->setMethod(PhpMethod::create('getTime')
            ->setVisibility('public')
            ->setReturnType('DateTime')
        );

        $class->setMethod(PhpMethod::create('getTimeZone')
            ->setVisibility('public')
            ->setReturnType('DateTimeZone')
        );

        $class->setMethod(PhpMethod::create('setTime')
            ->setVisibility('public')
            ->setReturnType('void')
            ->addParameter(PhpParameter::create('time')
                ->setType('DateTime')
            )
        );

        $class->setMethod(PhpMethod::create('setTimeZone')
            ->setVisibility('public')
            ->addParameter(PhpParameter::create('timezone')
                ->setType('DateTimeZone')
            )
        );

        $class->setMethod(PhpMethod::create('setArray')
            ->setVisibility('public')
            ->setReturnType('array')
            ->addParameter(PhpParameter::create('array')
                ->setDefaultValue(null)
                ->setPassedByReference(true)
                ->setType('array')
            )
        );

        $class->setMethod(PhpMethod::create('setArrayWithDefault')
            ->setVisibility('public')
            ->setReturnType('array')
            ->addParameter(PhpParameter::create('array')
                ->setDefaultValue([])
                ->setType('array')
            )
        );

        $class->setMethod(PhpMethod::create('getFoo')
            ->setReturnType(Foo::class, true)
        );

        $class->setMethod(PhpMethod::create('getBar')
            ->setReturnType(Bar::class)
        );

        $class->setMethod(PhpMethod::create('getBaz')
            ->setReturnType(Baz::class)
        );


        $this->assertEquals($class, PhpClass::fromReflection(new ReflectionClass(EntityPhp7::class)));
    }
}
