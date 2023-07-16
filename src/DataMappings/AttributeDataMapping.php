<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\DataMappings;

use Closure;
use Illuminate\Support\Stringable;
use NorseBlue\Xmlify\Contracts\DataMapping;

readonly class AttributeDataMapping implements DataMapping
{
    /**
     * @phpstan-param  Closure(string $accessor) : (Stringable|null) $value
     */
    public function __construct(
        public Stringable $target_name,
        public Stringable $namespace,
        public Stringable $source_name,
        public Stringable $source_type,
        public Closure $value,
    ) {
    }
}
