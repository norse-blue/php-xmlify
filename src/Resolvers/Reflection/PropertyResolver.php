<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers\Reflection;

use function collect;
use Illuminate\Support\Collection;
use NorseBlue\Xmlify\Concerns\CreatesResolverUsingReflectionClass;
use NorseBlue\Xmlify\DataTargets\AsXmlTarget;
use NorseBlue\Xmlify\Resolvers\ReflectionResolver;
use ReflectionAttribute;
use ReflectionProperty;

/**
 * @template T of object
 * @template TResolve of ReflectionProperty
 *
 * @implements ReflectionResolver<T, ReflectionProperty>
 */
class PropertyResolver implements ReflectionResolver
{
    /** @phpstan-use CreatesResolverUsingReflectionClass<T, ReflectionProperty> */
    use CreatesResolverUsingReflectionClass;

    /**
     * @return Collection<int, ReflectionProperty>
     */
    public function resolve(): Collection
    {
        return collect($this->reflection->getProperties())
            ->filter(static function (ReflectionProperty $property) {
                $attributes = $property->getAttributes();

                return collect($attributes)
                    ->filter(
                        fn (ReflectionAttribute $attribute) => is_subclass_of(
                            $attribute->getName(),
                            AsXmlTarget::class
                        )
                    )
                    ->isNotEmpty();
            });
    }
}
