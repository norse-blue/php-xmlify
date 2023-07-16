<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Tests\Types;

use NorseBlue\Xmlify\Concerns\HandlesXmlify;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataTargets\AsXmlAttribute;
use NorseBlue\Xmlify\DataTargets\AsXmlElement;
use NorseBlue\Xmlify\DataTargets\AsXmlRoot;

#[AsXmlRoot('Publication', namespace: 'publication', namespaces: ['publication' => 'https://www.example.com/publication.xsd'])]
class Publication implements Xmlifiable
{
    use HandlesXmlify;

    public function __construct(
        #[AsXmlAttribute(namespace: 'publication')]
        public string $isbn10,
        #[AsXmlAttribute(namespace: 'publication')]
        public ?string $isbn13 = null,
        #[AsXmlAttribute(namespace: 'publication')]
        public ?string $asin = null,
        #[AsXmlElement(namespace: 'publication')]
        public string $date = '',
        #[AsXmlElement(namespace: 'publication')]
        public string $publisher = '',
        #[AsXmlElement(namespace: 'publication')]
        public ?string $edition = null,
        #[AsXmlElement(namespace: 'publication')]
        public string $language = '',
        #[AsXmlElement(namespace: 'publication')]
        public string $type = '',
        #[AsXmlElement(namespace: 'publication')]
        public ?int $pages = null,
    ) {
    }
}
