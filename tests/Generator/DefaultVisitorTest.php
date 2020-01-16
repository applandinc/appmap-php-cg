<?php

namespace CG\Tests\Generator;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\DefaultVisitor;
use CG\Generator\PhpClass;
use CG\Generator\PhpFunction;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\RelativePath;
use CG\Generator\Writer;
use CG\Tests\Generator\Fixture\EntityPhp7;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class DefaultVisitorTest extends TestCase
{
    public function testVisitFunction(): void
    {
        $writer = new Writer();

        $function = new PhpFunction();
        $function
            ->setName('foo')
            ->addParameter(PhpParameter::create('a'))
            ->addParameter(PhpParameter::create('b'))
            ->setBody(
                $writer
                    ->writeln('if ($a === $b) {')
                    ->indent()
                    ->writeln('throw new \InvalidArgumentException(\'$a is not allowed to be the same as $b.\');')
                    ->outdent()
                    ->write("}\n\n")
                    ->write('return $b;')
                    ->getContent()
            )
            ->setDocblock(<<<DOC
/**
 * @param \$a
 * @param \$b
 * @return mixed
 * @author Michael Skvortsov <demoniac.death@gmail.com>
 */
DOC
            )
            ->setNamespace("Foo\Bar");

        $visitor = new DefaultVisitor();
        $visitor->visitFunction($function);

        $this->assertEquals($this->getContent('a_b_function.php'), $visitor->getContent());
    }

    public function testVisitMethod(): void
    {
        $method = new PhpMethod();
        $visitor = new DefaultVisitor();

        $method
            ->setName('foo')
            ->setReferenceReturned(true)
            ->setAbstract(true);
        $visitor->visitMethod($method);

        $this->assertEquals($this->getContent('reference_returned_method.php'), $visitor->getContent());
    }

    public function testVisitMethodWithCallable(): void
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('`callable` is only supported in PHP >=5.4.0');
        }

        $method = new PhpMethod();
        $parameter = new PhpParameter('bar');
        $parameter->setType('callable');

        $method
            ->setName('foo')
            ->addParameter($parameter);

        $visitor = new DefaultVisitor();
        $visitor->visitMethod($method);

        $this->assertEquals($this->getContent('callable_parameter.php'), $visitor->getContent());
    }

    /**
     * @throws ReflectionException
     */
    public function testVisitClassWithPhp7Features(): void
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Test only valid for PHP >=7.0');
        }

        $ref = new ReflectionClass(EntityPhp7::class);
        $class = PhpClass::fromReflection($ref);

        $generator = new DefaultGeneratorStrategy();
        $content = $generator->generate($class);


        $this->assertEquals($this->getContent('php7_class.php'), $content);
    }


    /**
     * @dataProvider visitFunctionWithPhp7FeaturesDataProvider
     * @param $filename
     * @param $function
     */
    public function testVisitFunctionWithPhp7Features($filename, $function): void
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Test only valid for PHP >=7.0');
        }

        $visitor = new DefaultVisitor();
        $visitor->visitFunction($function);

        $this->assertEquals($this->getContent($filename . '.php'), $visitor->getContent());

    }

    public function visitFunctionWithPhp7FeaturesDataProvider(): array
    {
        $builtinReturn = PhpFunction::create('foo')
            ->setReturnType('bool');
        $nonBuiltinReturn = PhpFunction::create('foo')
            ->setReturnType('\Foo');
        $nonBuiltinReturnUnescaped = PhpFunction::create('foo')
            ->setReturnType('Foo');


        return [
            ['php7_builtin_return', $builtinReturn],
            ['php7_func_non_builtin_return', $nonBuiltinReturn],
            ['php7_func_non_builtin_return', $nonBuiltinReturnUnescaped],
        ];
    }

    /**
     * @param string $filename
     * @return string|null
     */
    private function getContent($filename): ?string
    {
        if (!is_file($path = __DIR__ . '/Fixture/generated/' . $filename)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist.', $path));
        }

        return file_get_contents($path) ?: null;
    }

    /**
     * @param PhpClass $class
     * @param string $content
     * @dataProvider startVisitingClassDataProvider
     */
    public function testStartVisitingClass(PhpClass $class, string $content): void
    {
        $visitor = new DefaultVisitor();
        $visitor->startVisitingClass($class);
        $visitor->endVisitingClass($class);
        $this->assertEquals($content, $visitor->getContent());
    }

    public function startVisitingClassDataProvider(): array
    {
        return [
            [PhpClass::create('Foo')->addInterfaceName('ArrayAccess'), <<<CLASS
class Foo implements \ArrayAccess
{
}
CLASS
            ],
            [PhpClass::create('Foo')->setAbstract(true), <<<CLASS
abstract class Foo
{
}
CLASS
            ],
            [PhpClass::create('Foo')->setFinal(true), <<<CLASS
final class Foo
{
}
CLASS
            ],
            [PhpClass::create('Foo')->addUseStatement("Foo\Bar"), <<<CLASS
use Foo\Bar;

class Foo
{
}
CLASS
            ],
            [PhpClass::create('Foo')->addUseStatement("Foo\Bar", 'Bar'), <<<CLASS
use Foo\Bar;

class Foo
{
}
CLASS
            ],
            [PhpClass::create('Foo')->addUseStatement("Foo\Bar", 'Baz'), <<<CLASS
use Foo\Bar as Baz;

class Foo
{
}
CLASS
            ],
            [PhpClass::create('Foo')->addRequiredFile('foo.inc.php'), <<<CLASS
require_once 'foo.inc.php';

class Foo
{
}
CLASS
            ],
            [PhpClass::create('Foo')->addRequiredFile(new RelativePath('bar.inc.php')), <<<CLASS
require_once __DIR__ . '/bar.inc.php';

class Foo
{
}
CLASS
            ],
        ];
    }
}
