<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Enums;

use ReflectionMethod;
use ReflectionProperty;

enum DataMapperSource: string
{
    case Property = 'property';
    case Method = 'method';

    public static function fromReflection(ReflectionProperty|ReflectionMethod $reflection): self
    {
        return match (true) {
            $reflection instanceof ReflectionProperty => self::Property,
            $reflection instanceof ReflectionMethod => self::Method,
        };
    }
}
