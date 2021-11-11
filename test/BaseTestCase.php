<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;
use function dirname;

/**
 * Class BaseTestCase
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * @param string $path
     *
     * @return string
     */
    protected function getTestTplFile(string $path): string
    {
        return __DIR__ . '/' . $path;
    }

    /**
     * get method for test protected and private method
     *
     * usage:
     *
     * ```php
     * $rftMth = $this->method($className, $protectedOrPrivateMethod)
     *
     * $obj = new $className();
     * $res = $rftMth->invokeArgs($obj, $invokeArgs);
     * ```
     *
     * @param object|string $class
     * @param string $method
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected static function getMethod(object|string $class, string $method): ReflectionMethod
    {
        // $class  = new \ReflectionClass($class);
        // $rftMth = $class->getMethod($method);

        $rftMth = new \ReflectionMethod($class, $method);
        $rftMth->setAccessible(true);

        return $rftMth;
    }

    /**
     * @param callable $cb
     * @param mixed ...$args
     *
     * @return Throwable
     */
    protected function runAndGetException(callable $cb, ...$args): Throwable
    {
        try {
            $cb(...$args);
        } catch (Throwable $e) {
            return $e;
        }

        return new RuntimeException('NO ERROR', -1);
    }
}
