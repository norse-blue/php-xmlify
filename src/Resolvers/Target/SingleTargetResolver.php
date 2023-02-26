<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers\Target;

use NorseBlue\Xmlify\Contracts\DataMapping;
use NorseBlue\Xmlify\Resolvers\TargetResolver;

/**
 * @template T of object
 * @template TResolve of DataMapping
 *
 * @extends TargetResolver<T, TResolve>
 */
interface SingleTargetResolver extends TargetResolver
{
    public function resolve(): DataMapping;
}
