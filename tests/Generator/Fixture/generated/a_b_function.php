namespace Foo\Bar;

/**
 * @param $a
 * @param $b
 * @return mixed
 * @author Michael Skvortsov <demoniac.death@gmail.com>
 */
function foo($a, $b)
{
    if ($a === $b) {
        throw new \InvalidArgumentException('$a is not allowed to be the same as $b.');
    }

    return $b;
}