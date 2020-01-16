<?php

namespace CG\Tests\Proxy;

use CG\Core\DefaultGeneratorStrategy;
use CG\Core\NamingStrategyInterface;
use CG\Proxy\Enhancer;
use CG\Proxy\InterceptionGenerator;
use CG\Proxy\InterceptorLoaderInterface;
use CG\Proxy\LazyInitializerGenerator;
use CG\Proxy\LazyInitializerInterface;
use CG\Tests\Proxy\Fixture\Entity;
use CG\Tests\Proxy\Fixture\MarkerInterface;
use CG\Tests\Proxy\Fixture\SimpleClass;
use CG\Tests\Proxy\Fixture\SluggableInterface;
use CG\Tests\Proxy\Fixture\TraceInterceptor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class EnhancerTest extends TestCase
{
    /**
     * @dataProvider getGenerationTests
     * @param $class
     * @param $generatedClass
     * @param array $interfaces
     * @param array $generators
     * @throws ReflectionException
     */
    public function testGenerateClass($class, $generatedClass, array $interfaces, array $generators): void
    {
        $enhancer = new Enhancer(new ReflectionClass($class), $interfaces, $generators);
        $enhancer->setNamingStrategy($this->getNamingStrategy($generatedClass));
        $enhancer->setGeneratorStrategy(new DefaultGeneratorStrategy());

        $this->assertEquals($this->getContent(substr($generatedClass, strrpos($generatedClass, '\\') + 1)), $enhancer->generateClass());
    }

    public function getGenerationTests(): array
    {
        return [
            [SimpleClass::class, 'CG\Tests\Proxy\Fixture\SimpleClass__CG__Enhanced', ['CG\Tests\Proxy\Fixture\MarkerInterface'], []],
            [SimpleClass::class, 'CG\Tests\Proxy\Fixture\SimpleClass__CG__Sluggable', [SluggableInterface::class], []],
            [Entity::class, 'CG\Tests\Proxy\Fixture\Entity__CG__LazyInitializing', [], [
                new LazyInitializerGenerator(),
            ]]
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testInterceptionGenerator(): void
    {
        $enhancer = new Enhancer(new ReflectionClass(Entity::class), [], [
            $generator = new InterceptionGenerator()
        ]);
        $enhancer->setNamingStrategy($this->getNamingStrategy('CG\Tests\Proxy\Fixture\Entity__CG__Traceable_' . sha1(microtime(true))));
        $generator->setPrefix('');

        $traceable = $enhancer->createInstance();
        $traceable->setLoader($this->getLoader([
            $interceptor1 = new TraceInterceptor(),
            $interceptor2 = new TraceInterceptor(),
        ]));

        $this->assertEquals('foo', $traceable->getName());
        $this->assertEquals('foo', $traceable->getName());
        $this->assertEquals(2, count($interceptor1->getLog()));
        $this->assertEquals(2, count($interceptor2->getLog()));
    }

    /**
     * @throws ReflectionException
     */
    public function testLazyInitializerGenerator(): void
    {
        $enhancer = new Enhancer(new ReflectionClass(Entity::class), [], [
            $generator = new LazyInitializerGenerator(),
        ]);
        $generator->setPrefix('');

        $entity = $enhancer->createInstance();
        $entity->setLazyInitializer($initializer = new Initializer());
        $this->assertEquals('foo', $entity->getName());
        $this->assertSame($entity, $initializer->getLastObject());
    }

    private function getLoader(array $interceptors): InterceptorLoaderInterface
    {
        $loader = $this->createMock(InterceptorLoaderInterface::class);
        $loader
            ->expects($this->any())
            ->method('loadInterceptors')
            ->will($this->returnValue($interceptors));

        return $loader;
    }

    /**
     * @param string $file
     * @return string
     */
    private function getContent($file): ?string
    {
        return file_get_contents(__DIR__ . '/Fixture/generated/' . $file . '.php.gen') ?: null;
    }

    /**
     * @param string $name
     * @return NamingStrategyInterface
     */
    private function getNamingStrategy($name): NamingStrategyInterface
    {
        $namingStrategy = $this->createMock(NamingStrategyInterface::class);
        $namingStrategy
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue($name));

        return $namingStrategy;
    }
}

class Initializer implements LazyInitializerInterface
{
    private Entity $lastObject;

    public function initializeObject($object)
    {
        if ($object instanceof Entity) {
            $this->lastObject = $object;
        }
    }

    public function getLastObject(): Entity
    {
        return $this->lastObject;
    }
}
