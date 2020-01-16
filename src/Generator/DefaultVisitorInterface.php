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

/**
 * The visitor interface required by the DefaultNavigator.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface DefaultVisitorInterface
{
    /**
     * Resets the visitors internal state to allow re-using the same instance.
     *
     * @return void
     */
    public function reset(): void;

    /**
     * @param PhpClass $class
     * @return void
     */
    public function startVisitingClass(PhpClass $class): void;

    /**
     * @return void
     */
    public function startVisitingClassConstants(): void;

    /**
     * @param  PhpConstant $constant
     * @return void
     */
    public function visitClassConstant(PhpConstant $constant): void;

    /**
     * @return void
     */
    public function endVisitingClassConstants(): void;

    /**
     * @return void
     */
    public function startVisitingProperties(): void;

    /**
     * @param PhpProperty $property
     * @return void
     */
    public function visitProperty(PhpProperty $property): void;

    /**
     * @return void
     */
    public function endVisitingProperties(): void;

    /**
     * @return void
     */
    public function startVisitingMethods(): void;

    /**
     * @param PhpMethod $method
     * @return void
     */
    public function visitMethod(PhpMethod $method): void;

    /**
     * @return void
     */
    public function endVisitingMethods(): void;

    /**
     * @param PhpClass $class
     * @return void
     */
    public function endVisitingClass(PhpClass $class): void;

    /**
     * @param PhpFunction $function
     * @return void
     */
    public function visitFunction(PhpFunction $function): void;
}
