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

use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Represents a PHP parameter.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpParameter
{
    private $name;
    private $defaultValue;
    private $hasDefaultValue = false;
    private $passedByReference = false;
    private $type;
    private $typeBuiltin;
    private $typeNullable = false;

    /**
     * @param string|null $name
     * @return PhpParameter
     */
    public static function create($name = null): PhpParameter
    {
        return new static($name);
    }

    /**
     * @param ReflectionParameter $ref
     * @return PhpParameter
     * @throws ReflectionException
     */
    public static function fromReflection(ReflectionParameter $ref): PhpParameter
    {
        $parameter = new static();
        $parameter
            ->setName($ref->name)
            ->setPassedByReference($ref->isPassedByReference())
        ;

        if ($ref->isDefaultValueAvailable()) {
            $parameter->setDefaultValue($ref->getDefaultValue());
        }

        if (method_exists($ref, 'getType')) {
            if ($type = $ref->getType()) {
                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                } else {
                    $typeName = (string)$type;
                }
                $parameter->setType($typeName, $ref->allowsNull());
            }
        } else if ($ref->isArray()) {
            $parameter->setType('array', $ref->allowsNull());
        } elseif ($class = $ref->getClass()) {
            $parameter->setType($class->name, $ref->allowsNull());
        } elseif (method_exists($ref, 'isCallable') && $ref->isCallable()) {
            $parameter->setType('callable', $ref->allowsNull());
        }

        return $parameter;
    }

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return PhpParameter
     */
    public function setName($name): PhpParameter
    {
        $this->name = $name;

        return $this;
    }

    public function setDefaultValue($value): PhpParameter
    {
        $this->defaultValue = $value;
        $this->hasDefaultValue = true;

        return $this;
    }

    public function unsetDefaultValue(): PhpParameter
    {
        $this->defaultValue = null;
        $this->hasDefaultValue = false;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpParameter
     */
    public function setPassedByReference($bool): PhpParameter
    {
        $this->passedByReference = (Boolean) $bool;

        return $this;
    }

    /**
     * @param string $type
     * @param bool $nullable
     * @return PhpParameter
     */
    public function setType($type, $nullable = false): PhpParameter
    {
        $this->type = $type;
        $this->typeBuiltin = BuiltinType::isBuiltIn($type);
        $this->typeNullable = $nullable;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function isPassedByReference(): bool
    {
        return $this->passedByReference;
    }

    public function getType()
    {
        return $this->type;
    }

    public function hasType(): bool
    {
        return null !== $this->type;
    }

    public function hasBuiltinType()
    {
        return $this->typeBuiltin;
    }

    public function hasNullableType()
    {
        return $this->typeNullable;
    }
}
