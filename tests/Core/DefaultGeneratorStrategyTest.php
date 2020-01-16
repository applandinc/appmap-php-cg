<?php

namespace CG\Tests\Core;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\AbstractPhpMember;
use CG\Generator\DefaultNavigator;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;
use CG\Generator\PhpProperty;
use PHPUnit\Framework\TestCase;

class DefaultGeneratorStrategyTest extends TestCase
{
    public function testGenerate(): void
    {
        $strategy = new DefaultGeneratorStrategy();
        $strategy->setConstantSortFunc(static function ($a, $b) {
            return strcasecmp($a, $b);
        });
        $strategy->setPropertySortFunc(static function (AbstractPhpMember $a, AbstractPhpMember $b) {
            return strcasecmp($a->getName(), $b->getName());
        });
        $strategy->setMethodSortFunc(static function($a, $b) {return DefaultNavigator::defaultMethodSortFunc($a, $b);});

        $this->assertEquals(
            $this->getContent('GenerationTestClass_A.php'),
            $strategy->generate($this->getClass())
        );
    }

    public function testGenerateChangedConstantOrder(): void
    {
        $strategy = new DefaultGeneratorStrategy();
        $strategy->setConstantSortFunc(static function ($a, $b) {
            return strcasecmp($b, $a);
        });
        $strategy->setPropertySortFunc(static function (AbstractPhpMember $a, AbstractPhpMember $b) {
            return strcasecmp($a->getName(), $b->getName());
        });

        $this->assertEquals(
            $this->getContent('GenerationTestClass_B.php'),
            $strategy->generate($this->getClass())
        );
    }

    /**
     * @param string $file
     * @return null|string
     */
    private function getContent($file): ?string
    {
        return file_get_contents(__DIR__ . '/generated/' . $file) ?: null;
    }

    /**
     * @return PhpClass
     */
    private function getClass(): PhpClass
    {
        return PhpClass::create()
            ->setName('GenerationTestClass')
            ->setMethod(PhpMethod::create('a'))
            ->setMethod(PhpMethod::create('b')->setStatic(true))
            ->setProperty(PhpProperty::create('a'))
            ->setProperty(PhpProperty::create('b'))
            ->setConstant('a', 'foo')
            ->setConstant('b', 'bar');
    }
}
