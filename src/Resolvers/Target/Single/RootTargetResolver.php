<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers\Target\Single;

use NorseBlue\Xmlify\Concerns\CreatesResolverUsingDataMapper;
use NorseBlue\Xmlify\DataMappings\RootDataMapping;
use NorseBlue\Xmlify\DataTargets\AsXmlRoot;
use NorseBlue\Xmlify\Resolvers\Target\SingleTargetResolver;
use ReflectionException;

/**
 * @template T of object
 * @template TResolve of RootDataMapping
 *
 * @implements SingleTargetResolver<T, RootDataMapping>
 */
class RootTargetResolver implements SingleTargetResolver
{
    /** @phpstan-use CreatesResolverUsingDataMapper<T, RootDataMapping> */
    use CreatesResolverUsingDataMapper;

    /**
     * @throws ReflectionException
     */
    public function resolve(): RootDataMapping
    {
        $attribute = $this->mapper->reflection()->getAttributes(AsXmlRoot::class)[0] ?? null;

        return new RootDataMapping(
            target_name: str($attribute?->getArguments()['name']
                ?? $attribute?->getArguments()[0]
                ?? $this->mapper->reflection()->getShortName()),
        );
    }
}
