<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Concerns;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @template T of object
 * @template TResolve of ReflectionMethod|ReflectionProperty
 */
trait CreatesResolverUsingReflectionClass
{
    /**
     * @param  ReflectionClass<T>  $reflection
     */
    private function __construct(public readonly ReflectionClass $reflection)
    {
    }

    /**
     * @param  ReflectionClass<T>  $reflection
     * @return self<T, TResolve>
     */
    public static function using(ReflectionClass $reflection): self
    {
        return new self($reflection);
    }
}
