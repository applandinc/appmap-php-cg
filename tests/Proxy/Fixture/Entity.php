<?php

namespace CG\Tests\Proxy\Fixture;

class Entity
{
    public function getName(): string
    {
        return 'foo';
    }

    final public function getBaz(): void
    {
    }

    protected function getFoo(): void
    {
    }

    private function getBar(): void
    {
    }
}