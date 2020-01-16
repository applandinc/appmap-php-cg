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

use Closure;

/**
 * The default navigator.
 *
 * This class is responsible for the default traversal algorithm of the different
 * code elements.
 *
 * Unlike other visitor pattern implementations, this allows to separate the
 * traversal logic from the objects that are traversed.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultNavigator
{
    /**
     * @var Closure|null
     */
    private $constantSortFunc;
    /**
     * @var Closure|null
     */
    private $propertySortFunc;
    /**
     * @var Closure|null
     */
    private $methodSortFunc;

    /**
     * Sets a custom constant sorting function.
     *
     * @param null|Closure $func
     */
    public function setConstantSortFunc(Closure $func = null): void
    {
        $this->constantSortFunc = $func;
    }

    /**
     * Sets a custom property sorting function.
     *
     * @param null|Closure $func
     */
    public function setPropertySortFunc(Closure $func = null): void
    {
        $this->propertySortFunc = $func;
    }

    /**
     * Sets a custom method sorting function.
     *
     * @param null|Closure $func
     */
    public function setMethodSortFunc(Closure $func = null): void
    {
        $this->methodSortFunc = $func;
    }

    public function accept(DefaultVisitorInterface $visitor, PhpClass $class): void
    {
        $visitor->startVisitingClass($class);

        $constants = $class->getConstants(true);
        if (!empty($constants)) {
            uksort($constants, $this->getConstantSortFunc());

            $visitor->startVisitingClassConstants();
            foreach ($constants as $constant) {
                $visitor->visitClassConstant($constant);
            }
            $visitor->endVisitingClassConstants();
        }

        $properties = $class->getProperties();
        if (!empty($properties)) {
            usort($properties, $this->getPropertySortFunc());

            $visitor->startVisitingProperties();
            foreach ($properties as $property) {
                $visitor->visitProperty($property);
            }
            $visitor->endVisitingProperties();
        }

        $methods = $class->getMethods();
        if (!empty($methods)) {
            usort($methods, $this->getMethodSortFunc());

            $visitor->startVisitingMethods();
            foreach ($methods as $method) {
                $visitor->visitMethod($method);
            }
            $visitor->endVisitingMethods();
        }

        $visitor->endVisitingClass($class);
    }

    private function getConstantSortFunc(): callable
    {
        return $this->constantSortFunc ?: 'strcasecmp';
    }

    private function getMethodSortFunc(): callable
    {
        if (null !== $this->methodSortFunc) {
            return $this->methodSortFunc;
        }

        return [__CLASS__, 'defaultMethodSortFunc'];
    }

    public static function defaultMethodSortFunc(AbstractPhpMember $a, AbstractPhpMember $b): int
    {
        if ($a->isStatic() !== $isStatic = $b->isStatic()) {
            return $isStatic ? 1 : -1;
        }

        return self::defaultPropertySortFunc($a, $b);
    }

    private function getPropertySortFunc(): callable
    {
        if (null !== $this->propertySortFunc) {
            return $this->propertySortFunc;
        }

        return [__CLASS__, 'defaultPropertySortFunc'];
    }

    public static function defaultPropertySortFunc(AbstractPhpMember $a, AbstractPhpMember $b): int
    {
        $aScore = self::getMemberSortingScore($a);
        $bScore = self::getMemberSortingScore($b);
        if ($aScore !== $bScore) {
            return $aScore > $bScore ? -1 : 1;
        }

        return strcasecmp($b->getName(), $a->getName());
    }

    public static function getMemberSortingScore(AbstractPhpMember $member): ?int
    {
        switch ($member->getVisibility()) {
            case 'public': return 3;
            case 'protected': return 2;
            default: return 1;
        }
    }
}
