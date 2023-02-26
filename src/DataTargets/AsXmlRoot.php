<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\DataTargets;

use Attribute;

/**
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class AsXmlRoot implements AsXmlTarget
{
    public function __construct(public string $name)
    {
    }
}
