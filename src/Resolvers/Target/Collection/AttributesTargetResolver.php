<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers\Target\Collection;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use NorseBlue\Xmlify\Concerns\CreatesResolverUsingDataMapper;
use NorseBlue\Xmlify\DataMappings\AttributeDataMapping;
use NorseBlue\Xmlify\DataTargets\AsXmlAttribute;
use NorseBlue\Xmlify\Enums\DataMapperSource;
use NorseBlue\Xmlify\Resolvers\Target\CollectionTargetResolver;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

/**
 * @template T of object
 * @template TResolve of AttributeDataMapping
 *
 * @implements CollectionTargetResolver<T, AttributeDataMapping>
 */
class AttributesTargetResolver implements CollectionTargetResolver
{
    /** @phpstan-use CreatesResolverUsingDataMapper<T, AttributeDataMapping> */
    use CreatesResolverUsingDataMapper;

    /**
     * @return Collection<int, AttributeDataMapping>
     *
     * @throws ReflectionException
     */
    public function resolve(): Collection
    {
        return $this->mapper->reflections()
            ->filter(
                fn (ReflectionProperty|ReflectionMethod $reflected) => collect($reflected->getAttributes())
                    ->filter(
                        fn (ReflectionAttribute $attribute) => is_a($attribute->getName(), AsXmlAttribute::class, true),
                    )
                    ->isNotEmpty(),
            )
            ->map(function (ReflectionProperty|ReflectionMethod $reflected) {
                return $this->createAttributeDataMapping($reflected);
            })
            ->values();
    }

    private function createAttributeDataMapping(ReflectionProperty|ReflectionMethod $reflected): AttributeDataMapping
    {
        $attribute = $reflected->getAttributes(AsXmlAttribute::class)[0];
        $value_resolver = function (bool|int|float|array|string|object|null $value): Stringable|null {
            return match (true) {
                $value === null || $value instanceof Stringable => $value,
                is_array($value) || is_object($value) => str(json_encode($value)),
                default => str($value),
            };
        };

        return new AttributeDataMapping(
            target_name: str($attribute->getArguments()['name']
                ?? $attribute->getArguments()[0]
                ?? $reflected->getName()),

            source_name: str($reflected->getName()),

            source_type: str(DataMapperSource::fromReflection($reflected)->value),

            value: match (true) {
                $reflected instanceof ReflectionProperty => Closure::bind(fn (string $property): Stringable|null => $value_resolver($this->{$property}), new stdClass()),
                $reflected instanceof ReflectionMethod => Closure::bind(fn (string $method): Stringable|null => $value_resolver($this->{$method}()), new stdClass()),
            },
        );
    }
}
