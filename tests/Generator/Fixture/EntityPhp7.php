<?php /** @noinspection ALL */

namespace CG\Tests\Generator\Fixture;

use CG\Tests\Generator\Fixture\SubFixture as Sub;
use CG\Tests\Generator\Fixture\SubFixture\Baz;
use CG\Tests\Generator\Fixture\SubFixture\Foo;
use DateTime;
use DateTimeZone;

/**
 * Doc Comment.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class EntityPhp7
{
    /**
     * @var integer
     */
    private $id = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EntityPhp7
     */
    public function setId(int $id = null): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTime(): DateTime
    {
        return null;
    }

    public function getTimeZone(): DateTimeZone
    {
        return null;
    }

    public function setTime(DateTime $time): void
    {
    }

    public function setTimeZone(DateTimeZone $timezone)
    {
    }

    public function setArray(array &$array = null): array
    {
        return null;
    }

    public function setArrayWithDefault(array $array = []): array
    {
        return null;
    }

    public function getFoo(): ?Foo
    {
        return null;
    }

    public function getBar(): Sub\Bar
    {
        return null;
    }

    public function getBaz(): Baz
    {
        return null;
    }
}
