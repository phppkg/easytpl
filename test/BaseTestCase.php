<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;

/**
 * Class BaseTestCase
 *
 * @package Toolkit\PFlagTest
 */
abstract class BaseTestCase extends TestCase
{

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
     * @param string|object $class
     * @param string $method
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected static function getMethod($class, string $method): ReflectionMethod
    {
        // $class  = new \ReflectionClass($class);
        // $method = $class->getMethod($method);

        $method = new \ReflectionMethod($class, $method);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param callable $cb
     *
     * @return Throwable
     */
    protected function runAndGetException(callable $cb): Throwable
    {
        try {
            $cb();
        } catch (Throwable $e) {
            return $e;
        }

        return new RuntimeException('NO ERROR', -1);
    }
}
