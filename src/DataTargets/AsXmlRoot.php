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
    /**
     * @var array<string, string>
     */
    public array $namespaces;

    /**
     * @param  array<string, string>  $namespaces
     */
    public function __construct(public string $name, public string $namespace = '', array $namespaces = [])
    {
        ksort($namespaces, SORT_NATURAL);
        $this->namespaces = $namespaces;
    }
}
