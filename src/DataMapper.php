<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify;

use Illuminate\Support\Collection;
use NorseBlue\Xmlify\DataMappings\AttributeDataMapping;
use NorseBlue\Xmlify\DataMappings\ElementDataMapping;
use NorseBlue\Xmlify\DataMappings\RootDataMapping;
use NorseBlue\Xmlify\Enums\DataMapperTarget;
use NorseBlue\Xmlify\Resolvers\Reflection\MethodResolver;
use NorseBlue\Xmlify\Resolvers\Reflection\PropertyResolver;
use NorseBlue\Xmlify\Resolvers\Target\Collection\AttributesTargetResolver;
use NorseBlue\Xmlify\Resolvers\Target\Collection\ElementsTargetResolver;
use NorseBlue\Xmlify\Resolvers\Target\Single\RootTargetResolver;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @template T of object
 */
class DataMapper
{
    /**
     * @var array<DataMapper<T>>
     */
    private static array $mappers = [];

    private RootDataMapping $root_mapping;

    /**
     * @var Collection<int, AttributeDataMapping>
     */
    private Collection $attribute_mappings;

    /**
     * @var Collection<int, ElementDataMapping>
     */
    private Collection $element_mappings;

    /**
     * @var ?ReflectionClass<T>
     */
    private ?ReflectionClass $reflection;

    /**
     * @var Collection<int, ReflectionMethod>
     */
    private Collection $methods;

    /**
     * @var Collection<int, ReflectionProperty>
     */
    private Collection $properties;

    /**
     * @param  class-string<T>  $class
     */
    private function __construct(public readonly string $class)
    {
    }

    /**
     * @param  class-string<T>  $class
     * @return DataMapper<T>
     */
    public static function for(string $class): self
    {
        self::$mappers[$class] ??= new self($class);

        return self::$mappers[$class];
    }

    /**
     * @return ReflectionClass<object|T>
     *
     * @throws ReflectionException
     */
    public function reflection(): ReflectionClass
    {
        $this->reflection ??= new ReflectionClass($this->class);

        return $this->reflection;
    }

    /**
     * @throws ReflectionException
     */
    public function root(): RootDataMapping
    {
        $this->root_mapping ??= RootTargetResolver::using($this)->resolve();

        return $this->root_mapping;
    }

    /**
     * @return Collection<int, AttributeDataMapping>
     *
     * @throws ReflectionException
     */
    public function attributes(): Collection
    {
        $this->attribute_mappings ??= AttributesTargetResolver::using($this)->resolve();

        return $this->attribute_mappings;
    }

    /**
     * @return Collection<int, ElementDataMapping>
     *
     * @throws ReflectionException
     */
    public function elements(): Collection
    {
        $this->element_mappings ??= ElementsTargetResolver::using($this)->resolve();

        return $this->element_mappings;
    }

    /**
     * @return array{
     *     root: RootDataMapping,
     *     attribute: Collection<int, AttributeDataMapping>,
     *     element: Collection<int, ElementDataMapping>,
     * }
     *
     * @throws ReflectionException
     */
    public function mappings(): array
    {
        return [
            DataMapperTarget::Root->value => $this->root(),
            DataMapperTarget::Attribute->value => $this->attributes(),
            DataMapperTarget::Element->value => $this->elements(),
        ];
    }

    /**
     * @return Collection<int, ReflectionMethod>
     *
     * @throws ReflectionException
     */
    public function methods(): Collection
    {
        $this->methods ??= (MethodResolver::using($this->reflection()))->resolve();

        return $this->methods;
    }

    /**
     * @return Collection<int, ReflectionProperty>
     *
     * @throws ReflectionException
     */
    public function properties(): Collection
    {
        $this->properties ??= (PropertyResolver::using($this->reflection()))->resolve();

        return $this->properties;
    }

    /**
     * @return Collection<int, ReflectionProperty|ReflectionMethod>
     *
     * @throws ReflectionException
     */
    public function reflections(): Collection
    {
        return collect()
            ->merge($this->properties())
            ->merge($this->methods());
    }
}
