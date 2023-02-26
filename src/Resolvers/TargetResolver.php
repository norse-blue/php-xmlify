<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers;

use NorseBlue\Xmlify\Contracts\DataMapping;
use NorseBlue\Xmlify\DataMapper;

/**
 * @template T of object
 * @template TResolve of DataMapping
 */
interface TargetResolver
{
    /**
     * @param  DataMapper<T>  $mapper
     * @return self<T, TResolve>
     */
    public static function using(DataMapper $mapper): self;
}
