<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\DataMappings;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use NorseBlue\Xmlify\Contracts\DataMapping;
use NorseBlue\Xmlify\Contracts\Xmlifiable;

readonly class ElementDataMapping implements DataMapping
{
    /**
     * @phpstan-param  Closure(string $accessor) : (array<mixed>|Collection<int, mixed>|Xmlifiable|Stringable|null) $value
     * @param  array<string, string>  $namespaces
     */
    public function __construct(
        public Stringable $target_name,
        public Stringable $namespace,
        public Stringable $source_name,
        public Stringable $source_type,
        public Closure $value,
        public array $namespaces = [],
        public bool $asCData = false,
    ) {
    }
}
