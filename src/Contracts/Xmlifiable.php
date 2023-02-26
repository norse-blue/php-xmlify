<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Contracts;

use DOMDocument;
use NorseBlue\Xmlify\Enums\ToXmlOption;

interface Xmlifiable
{
    public function ToXml(bool $unformatted = false, ToXmlOption ...$options): string;

    public function ToXmlDom(): DOMDocument;

    public function __toString(): string;
}
