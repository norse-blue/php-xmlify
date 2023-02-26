<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\DataTargets;

use Attribute;

/**
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
readonly class AsXmlElement implements AsXmlTarget
{
    public function __construct(public ?string $name = null, public ?string $prefix = null)
    {
    }
}
