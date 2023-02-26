<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @template T of object
 * @template TResolve of ReflectionMethod|ReflectionProperty
 */
interface ReflectionResolver
{
    /**
     * @param  ReflectionClass<T>  $reflection
     * @return self<T, TResolve>
     */
    public static function using(ReflectionClass $reflection): self;

    /**
     * @return Collection<int, TResolve>
     */
    public function resolve(): Collection;
}
