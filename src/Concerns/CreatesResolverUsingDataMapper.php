<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Concerns;

use NorseBlue\Xmlify\Contracts\DataMapping;
use NorseBlue\Xmlify\DataMapper;

/**
 * @template T of object
 * @template TResolve of DataMapping
 */
trait CreatesResolverUsingDataMapper
{
    /**
     * @param  DataMapper<T>  $mapper
     */
    private function __construct(public readonly DataMapper $mapper)
    {
    }

    /**
     * @param  DataMapper<T>  $mapper
     * @return self<T, TResolve>
     */
    public static function using(DataMapper $mapper): self
    {
        return new self($mapper);
    }
}
