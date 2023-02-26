<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Resolvers\Target\Collection;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use NorseBlue\Xmlify\Concerns\CreatesResolverUsingDataMapper;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataMappings\ElementDataMapping;
use NorseBlue\Xmlify\DataTargets\AsXmlCDataElement;
use NorseBlue\Xmlify\DataTargets\AsXmlElement;
use NorseBlue\Xmlify\Enums\DataMapperSource;
use NorseBlue\Xmlify\Resolvers\Target\CollectionTargetResolver;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

/**
 * @template T of object
 * @template TResolve of ElementDataMapping
 *
 * @implements CollectionTargetResolver<T, ElementDataMapping>
 */
class ElementsTargetResolver implements CollectionTargetResolver
{
    /** @phpstan-use CreatesResolverUsingDataMapper<T, ElementDataMapping> */
    use CreatesResolverUsingDataMapper;

    /**
     * @return Collection<int, ElementDataMapping>
     *
     * @throws ReflectionException
     */
    public function resolve(): Collection
    {
        return $this->mapper->reflections()
            ->filter(
                fn (ReflectionProperty|ReflectionMethod $reflected) => collect($reflected->getAttributes())
                    ->filter(
                        fn (ReflectionAttribute $attribute) => is_a($attribute->getName(), AsXmlElement::class, true),
                    )
                    ->isNotEmpty(),
            )
            ->map(function (ReflectionProperty|ReflectionMethod $reflected) {
                return $this->createElementDataMapping($reflected);
            })
            ->values();
    }

    private function createElementDataMapping(ReflectionProperty|ReflectionMethod $reflected): ElementDataMapping
    {
        $attribute = $reflected->getAttributes(AsXmlElement::class, ReflectionAttribute::IS_INSTANCEOF)[0];
        /** @return array|Collection<int, mixed>|Xmlifiable|Stringable|null */
        $value_resolver = function (bool|int|float|array|string|object|null $value): array|Collection|Xmlifiable|Stringable|null {
            return match (true) {
                $value === null || $value instanceof Stringable || $value instanceof Xmlifiable || is_array($value) || $value instanceof Collection => $value,
                is_object($value) => str(json_encode($value)),
                default => str($value),
            };
        };

        return new ElementDataMapping(
            target_name: str($attribute->getArguments()['name']
                ?? $attribute->getArguments()[0]
                ?? $reflected->getName()),

            source_name: str($reflected->getName()),

            source_type: str(DataMapperSource::fromReflection($reflected)->value),

            value: match (true) {
                $reflected instanceof ReflectionProperty => Closure::bind(fn (string $property): array|Collection|Xmlifiable|Stringable|null => $value_resolver($this->{$property}), new stdClass()),
                $reflected instanceof ReflectionMethod => Closure::bind(fn (string $method): array|Collection|Xmlifiable|Stringable|null => $value_resolver($this->{$method}()), new stdClass()),
            },

            asCData: $attribute->getName() === AsXmlCDataElement::class,
        );
    }
}
