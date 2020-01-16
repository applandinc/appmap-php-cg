<?php

namespace CG\Tests\Core;

use CG\Core\ClassUtils;
use PHPUnit\Framework\TestCase;

class ClassUtilsTest extends TestCase
{
    public function testGetUserClassName(): void
    {
        $this->assertEquals('Foo', ClassUtils::getUserClass('Foo'));
        $this->assertEquals('Bar', ClassUtils::getUserClass('FOO\__CG__\Bar'));
    }
}