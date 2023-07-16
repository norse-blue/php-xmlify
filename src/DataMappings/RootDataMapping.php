<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\DataMappings;

use Illuminate\Support\Stringable;
use NorseBlue\Xmlify\Contracts\DataMapping;

readonly class RootDataMapping implements DataMapping
{
    /**
     * @param  array<string, string>  $namespaces
     */
    public function __construct(
        public Stringable $target_name,
        public Stringable $namespace,
        public array $namespaces = [],
    ) {
    }
}
