<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CG\Generator;

use CG\Core\ReflectionUtils;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Represents a PHP class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpClass
{
    private $name;
    private $parentClassName;
    private $interfaceNames = [];
    private $useStatements = [];
    private $constants = [];
    private $properties = [];
    private $requiredFiles = [];
    private $methods = [];
    private $abstract = false;
    private $final = false;
    private $docblock;

    public static function create($name = null): PhpClass
    {
        return new self($name);
    }

    /**
     * @param ReflectionClass $ref
     * @return PhpClass
     * @throws ReflectionException
     */
    public static function fromReflection(ReflectionClass $ref): PhpClass
    {
        $class = new static();
        $class
            ->setName($ref->name)
            ->setAbstract($ref->isAbstract())
            ->setFinal($ref->isFinal())
            ->setConstants($ref->getConstants())
        ;

        if ($docComment = $ref->getDocComment()) {
            $class->setDocblock(ReflectionUtils::getUnindentedDocComment($docComment));
        }

        foreach ($ref->getMethods() as $method) {
            $class->setMethod(static::createMethod($method));
        }

        foreach ($ref->getProperties() as $property) {
            $class->setProperty(static::createProperty($property));
        }

        return $class;
    }

    /**
     * @param ReflectionMethod $method
     * @return PhpMethod
     * @throws ReflectionException
     */
    protected static function createMethod(ReflectionMethod $method): PhpMethod
    {
        return PhpMethod::fromReflection($method);
    }

    /**
     * @param ReflectionProperty $property
     * @return PhpProperty
     */
    protected static function createProperty(ReflectionProperty $property): PhpProperty
    {
        return PhpProperty::fromReflection($property);
    }

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return PhpClass
     */
    public function setName($name): PhpClass
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string|null $name
     * @return PhpClass
     */
    public function setParentClassName($name): PhpClass
    {
        $this->parentClassName = $name;

        return $this;
    }

    public function setInterfaceNames(array $names): PhpClass
    {
        $this->interfaceNames = $names;

        return $this;
    }

    /**
     * @param string $name
     * @return PhpClass
     */
    public function addInterfaceName($name): PhpClass
    {
        $this->interfaceNames[] = $name;

        return $this;
    }

    public function setRequiredFiles(array $files): PhpClass
    {
        $this->requiredFiles = $files;

        return $this;
    }

    /**
     * @param string $file
     * @return PhpClass
     */
    public function addRequiredFile($file): PhpClass
    {
        $this->requiredFiles[] = $file;

        return $this;
    }

    public function setUseStatements(array $useStatements): PhpClass
    {
        foreach ($useStatements as $alias => $namespace) {
            if (!is_string($alias)) {
                $alias = null;
            }
            $this->addUseStatement($namespace, $alias);
        }

        return $this;
    }

    /**
     * @param string $namespace
     * @param string|null $alias
     * @return PhpClass
     */
    public function addUseStatement($namespace, $alias = null): PhpClass
    {
        if (null === $alias) {
            $alias = substr($namespace, strrpos($namespace, '\\') + 1);
        }

        $this->useStatements[$alias] = $namespace;

        return $this;
    }

    public function setConstants(array $constants): PhpClass
    {
        $normalizedConstants = [];
        foreach ($constants as $name => $value) {
            if ( ! $value instanceof PhpConstant) {
                $constValue = $value;
                $value = new PhpConstant($name);
                $value->setValue($constValue);
            }

            $normalizedConstants[$name] = $value;
        }

        $this->constants = $normalizedConstants;

        return $this;
    }

    /**
     * @param $nameOrConstant
     * @param string $value
     * @return PhpClass
     */
    public function setConstant($nameOrConstant, $value = null): PhpClass
    {
        if ($nameOrConstant instanceof PhpConstant) {
            if (null !== $value) {
                throw new InvalidArgumentException('If a PhpConstant object is passed, $value must be null.');
            }

            $name = $nameOrConstant->getName();
            $constant = $nameOrConstant;
        } else {
            $name = $nameOrConstant;
            $constant = new PhpConstant($nameOrConstant);
            $constant->setValue($value);
        }

        $this->constants[$name] = $constant;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasConstant($name): bool
    {
        return array_key_exists($name, $this->constants);
    }

    /**
     * Returns a constant.
     *
     * @param string $name
     *
     * @return PhpConstant
     */
    public function getConstant($name): PhpConstant
    {
        if ( ! isset($this->constants[$name])) {
            throw new InvalidArgumentException(sprintf('The constant "%s" does not exist.', $name));
        }

        return $this->constants[$name];
    }

    /**
     * @param string $name
     * @return PhpClass
     */
    public function removeConstant($name): PhpClass
    {
        if (!array_key_exists($name, $this->constants)) {
            throw new InvalidArgumentException(sprintf('The constant "%s" does not exist.', $name));
        }

        unset($this->constants[$name]);

        return $this;
    }

    public function setProperties(array $properties): PhpClass
    {
        $this->properties = $properties;

        return $this;
    }

    public function setProperty(PhpProperty $property): PhpClass
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $property
     * @return bool
     */
    public function hasProperty(string $property): bool
    {
        return isset($this->properties[$property]);
    }

    /**
     * @param string $property
     * @return PhpClass
     */
    public function removeProperty(string $property): PhpClass
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new InvalidArgumentException(sprintf('The property "%s" does not exist.', $property));
        }
        unset($this->properties[$property]);

        return $this;
    }

    public function setMethods(array $methods): PhpClass
    {
        $this->methods = $methods;

        return $this;
    }

    public function setMethod(PhpMethod $method): PhpClass
    {
        $this->methods[$method->getName()] = $method;

        return $this;
    }

    public function getMethod($method)
    {
        if ( ! isset($this->methods[$method])) {
            throw new InvalidArgumentException(sprintf('The method "%s" does not exist.', $method));
        }

        return $this->methods[$method];
    }

    /**
     * @param string|PhpMethod $method
     * @return bool
     */
    public function hasMethod($method): bool
    {
        return isset($this->methods[$method]);
    }

    /**
     * @param string|PhpMethod $method
     * @return PhpClass
     */
    public function removeMethod($method): PhpClass
    {
        if (!array_key_exists($method, $this->methods)) {
            throw new InvalidArgumentException(sprintf('The method "%s" does not exist.', $method));
        }
        unset($this->methods[$method]);

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpClass
     */
    public function setAbstract($bool): PhpClass
    {
        $this->abstract = (Boolean) $bool;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpClass
     */
    public function setFinal($bool): PhpClass
    {
        $this->final = (Boolean) $bool;

        return $this;
    }

    /**
     * @param string $block
     * @return PhpClass
     */
    public function setDocblock($block): PhpClass
    {
        $this->docblock = $block;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParentClassName()
    {
        return $this->parentClassName;
    }

    public function getInterfaceNames(): array
    {
        return $this->interfaceNames;
    }

    public function getRequiredFiles(): array
    {
        return $this->requiredFiles;
    }

    public function getUseStatements(): array
    {
        return $this->useStatements;
    }

    public function getNamespace()
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return null;
        }

        return substr($this->name, 0, $pos);
    }

    public function getShortName()
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return $this->name;
        }

        return substr($this->name, $pos+1);
    }

    public function getConstants($asObjects = false): array
    {
        if ($asObjects) {
            return $this->constants;
        }

        return array_map(static function(PhpConstant $constant) {
            return $constant->getValue();
        }, $this->constants);
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function getDocblock()
    {
        return $this->docblock;
    }

    public function hasUseStatements(): bool
    {
        return count($this->getUseStatements()) > 0;
    }

    public function uses($typeDef): bool
    {
        if (empty($typeDef)) {
            throw new InvalidArgumentException('Empty type definition name given in ' . __METHOD__);
        }

        if (!$this->hasUseStatements()) {
            return false;
        }

        if ('\\' === $typeDef[0]) {
            return false; // typedef references the root
        }

        $parts = explode('\\', $typeDef);
        $typeDef = array_shift($parts);
        return isset($this->useStatements[$typeDef]);
    }
}
