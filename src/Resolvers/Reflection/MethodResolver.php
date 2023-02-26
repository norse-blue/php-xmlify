<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers\Reflection;

use function collect;
use Illuminate\Support\Collection;
use NorseBlue\Xmlify\Concerns\CreatesResolverUsingReflectionClass;
use NorseBlue\Xmlify\DataTargets\AsXmlTarget;
use NorseBlue\Xmlify\Resolvers\ReflectionResolver;
use ReflectionAttribute;
use ReflectionMethod;

/**
 * @template T of object
 * @template TResolve of ReflectionMethod
 *
 * @implements ReflectionResolver<T, ReflectionMethod>
 */
class MethodResolver implements ReflectionResolver
{
    /** @phpstan-use CreatesResolverUsingReflectionClass<T, ReflectionMethod> */
    use CreatesResolverUsingReflectionClass;

    private const REJECT_METHODS = ['__construct', 'ToXml', 'ToXmlDoc'];

    /**
     * @return Collection<int, ReflectionMethod>
     */
    public function resolve(): Collection
    {
        return collect($this->reflection->getMethods())
            ->reject(fn ($method) => in_array($method->name, self::REJECT_METHODS, true))
            ->filter(static function (ReflectionMethod $method) {
                $attributes = $method->getAttributes();

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
