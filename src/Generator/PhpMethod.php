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
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Represents a PHP method.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpMethod extends AbstractPhpMember
{
    private $final = false;
    private $abstract = false;
    private $parameters = [];
    private $referenceReturned = false;
    private $returnType;
    private $returnTypeBuiltin = false;
    private $body = '';
    /**
     * @var bool
     */
    private $nullAllowedForReturnType = false;

    /**
     * @param string|null $name
     * @return PhpMethod
     */
    public static function create($name = null): PhpMethod
    {
        return new static($name);
    }

    /**
     * @param ReflectionMethod $ref
     * @return PhpMethod
     * @throws ReflectionException
     */
    public static function fromReflection(ReflectionMethod $ref): PhpMethod
    {
        $method = new static();
        $method
            ->setFinal($ref->isFinal())
            ->setAbstract($ref->isAbstract())
            ->setStatic($ref->isStatic())
            ->setVisibility(self::getVisibilityFromReflection($ref))
            ->setReferenceReturned($ref->returnsReference())
            ->setName($ref->name)
        ;

        if (method_exists($ref, 'getReturnType') && $type = $ref->getReturnType()) {
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
            } else {
                $typeName = (string)$type;
            }
            $method->setReturnType($typeName, $type->allowsNull());
        }

        if ($docComment = $ref->getDocComment()) {
            $method->setDocblock(ReflectionUtils::getUnindentedDocComment($docComment));
        }

        foreach ($ref->getParameters() as $param) {
            $method->addParameter(static::createParameter($param));
        }

        // FIXME: Extract body?
        return $method;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return PhpParameter
     * @throws ReflectionException
     */
    protected static function createParameter(ReflectionParameter $parameter): PhpParameter
    {
        return PhpParameter::fromReflection($parameter);
    }

    /**
     * @param ReflectionMethod $ref
     * @return string
     */
    public static function getVisibilityFromReflection(ReflectionMethod $ref): string
    {
        if ($ref->isPublic()) {
            return self::VISIBILITY_PUBLIC;
        }

        if ($ref->isProtected()) {
            return self::VISIBILITY_PROTECTED;
        }

        return self::VISIBILITY_PRIVATE;
    }

    /**
     * @param boolean $bool
     * @return PhpMethod
     */
    public function setFinal($bool): PhpMethod
    {
        $this->final = (Boolean) $bool;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpMethod
     */
    public function setAbstract($bool): PhpMethod
    {
        $this->abstract = $bool;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpMethod
     */
    public function setReferenceReturned($bool): PhpMethod
    {
        $this->referenceReturned = (Boolean) $bool;

        return $this;
    }

    /**
     * @param string $body
     * @return PhpMethod
     */
    public function setBody($body): PhpMethod
    {
        $this->body = $body;

        return $this;
    }

    public function setParameters(array $parameters): PhpMethod
    {
        $this->parameters = array_values($parameters);

        return $this;
    }

    public function addParameter(PhpParameter $parameter): PhpMethod
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function setReturnType(string $type, $nullAllowed = false): PhpMethod
    {
        $this->returnType = $type;
        $this->returnTypeBuiltin = BuiltinType::isBuiltin($type);
        $this->nullAllowedForReturnType = $nullAllowed;
        return $this;
    }

    public function replaceParameter($position, PhpParameter $parameter): PhpMethod
    {
        if ($position < 0 || $position > count($this->parameters)) {
            throw new InvalidArgumentException(sprintf('The position must be in the range [0, %d].', count($this->parameters)));
        }
        $this->parameters[$position] = $parameter;

        return $this;
    }

    /**
     * @param integer $position
     * @return PhpMethod
     */
    public function removeParameter($position): PhpMethod
    {
        if (!isset($this->parameters[$position])) {
            throw new InvalidArgumentException(sprintf('There is no parameter at position "%d" does not exist.', $position));
        }
        unset($this->parameters[$position]);
        $this->parameters = array_values($this->parameters);

        return $this;
    }

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    public function isReferenceReturned(): bool
    {
        return $this->referenceReturned;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getReturnType()
    {
        return $this->returnType;
    }

    public function hasReturnType(): bool
    {
        return null !== $this->getReturnType();
    }

    public function hasBuiltInReturnType(): bool
    {
        return $this->returnTypeBuiltin;
    }

    public function isNullAllowedForReturnType(): bool
    {
        return $this->nullAllowedForReturnType;
    }

    public function setName($name): PhpMethod
    {
        parent::setName($name);
        return $this;
    }

    public function setVisibility($visibility): PhpMethod
    {
        parent::setVisibility($visibility);
        return $this;
    }

    public function setStatic($bool): PhpMethod
    {
        parent::setStatic($bool);
        return $this;
    }

    public function setDocblock($doc): PhpMethod
    {
        parent::setDocblock($doc);
        return $this;
    }
}
