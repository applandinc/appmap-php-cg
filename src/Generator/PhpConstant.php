<?php

namespace CG\Generator;

class PhpConstant
{
    private $name;
    private $value;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public static function create(string $name = null): PhpConstant
    {
        return new self($name);
    }

    public function setName($name): PhpConstant
    {
        $this->name = $name;

        return $this;
    }

    public function setValue($value): PhpConstant
    {
        $this->value = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}