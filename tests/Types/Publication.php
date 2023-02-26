<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Tests\Types;

use NorseBlue\Xmlify\Concerns\HandlesXmlify;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataTargets\AsXmlAttribute;
use NorseBlue\Xmlify\DataTargets\AsXmlElement;
use NorseBlue\Xmlify\DataTargets\AsXmlRoot;

#[AsXmlRoot('publication')]
class Publication implements Xmlifiable
{
    use HandlesXmlify;

    public function __construct(
        #[AsXmlAttribute]
        public string $isbn10,
        #[AsXmlAttribute]
        public ?string $isbn13 = null,
        #[AsXmlAttribute]
        public ?string $asin = null,
        #[AsXmlElement]
        public string $date = '',
        #[AsXmlElement]
        public string $publisher = '',
        #[AsXmlElement]
        public ?string $edition = null,
        #[AsXmlElement]
        public string $language = '',
        #[AsXmlElement]
        public string $type = '',
        #[AsXmlElement]
        public ?int $pages = null,
    ) {
    }
}
