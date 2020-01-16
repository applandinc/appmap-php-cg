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
use ReflectionProperty;

/**
 * Represents a PHP property.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpProperty extends AbstractPhpMember
{
    private $hasDefaultValue = false;
    private $defaultValue;

    /**
     * @param string|null $name
     * @return PhpProperty
     */
    public static function create($name = null): PhpProperty
    {
        return new static($name);
    }

    public static function fromReflection(ReflectionProperty $ref): PhpProperty
    {
        $property = new static();
        $property
            ->setName($ref->name)
            ->setStatic($ref->isStatic())
            ->setVisibility(self::getVisibilityFromReflection($ref))
        ;

        if ($docComment = $ref->getDocComment()) {
            $property->setDocblock(ReflectionUtils::getUnindentedDocComment($docComment));
        }

        $defaultProperties = $ref->getDeclaringClass()->getDefaultProperties();
        if (isset($defaultProperties[$ref->name])) {
            $property->setDefaultValue($defaultProperties[$ref->name]);
        }

        return $property;
    }

    /**
     * @param ReflectionProperty $ref
     * @return string
     */
    public static function getVisibilityFromReflection(ReflectionProperty $ref): string
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
     * @param string|false $value
     * @return PhpProperty
     */
    public function setDefaultValue($value): PhpProperty
    {
        $this->defaultValue = $value;
        $this->hasDefaultValue = true;

        return $this;
    }

    public function unsetDefaultValue(): PhpProperty
    {
        $this->hasDefaultValue = false;
        $this->defaultValue = null;

        return $this;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }


    public function setName($name): PhpProperty
    {
        parent::setName($name);
        return $this;
    }

    public function setVisibility($visibility): PhpProperty
    {
        parent::setVisibility($visibility);
        return $this;
    }

    public function setStatic($bool): PhpProperty
    {
        parent::setStatic($bool);
        return $this;
    }

    public function setDocblock($doc): PhpProperty
    {
        parent::setDocblock($doc);
        return $this;
    }
}
