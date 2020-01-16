<?php

namespace CG\Tests\Core;

use CG\Core\ReflectionUtils;
use CG\Generator\Writer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReflectionUtilsTest extends TestCase
{
    public function testGetOverridableMethods(): void
    {
        $ref = new ReflectionClass(OverridableReflectionTest::class);
        $methods = ReflectionUtils::getOverrideableMethods($ref);

        $this->assertCount(4, $methods);

        $methods = array_map(static function ($v) {
            return $v->name;
        }, $methods);
        sort($methods);
    }

    public function testGetUnIndentedDocComment(): void
    {
        $writer = new Writer();
        $comment = $writer
            ->writeln('/**')
            ->indent()
            ->writeln(' * Foo.')
            ->write(' */')
            ->getContent();

        $this->assertEquals("/**\n * Foo.\n */", ReflectionUtils::getUnindentedDocComment($comment));
    }
}

abstract class OverridableReflectionTest
{
    /** @noinspection PhpUnused */
    public function a(): void
    {
    }

    /** @noinspection PhpUnused */
    final public function b(): void
    {
    }

    /** @noinspection PhpUnused */
    public static function c(): void
    {
    }

    /** @noinspection PhpUnused */
    abstract public function d();

    /** @noinspection PhpUnused */
    protected function e(): void
    {
    }

    /** @noinspection PhpUnused */
    final protected function f(): void
    {
    }

    /** @noinspection PhpUnused */
    protected static function g(): void
    {
    }

    /** @noinspection PhpUnused */
    abstract protected function h();

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /** @noinspection PhpUnused */
    private function i(): void
    {
    }
}
