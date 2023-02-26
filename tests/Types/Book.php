<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Tests\Types;

use NorseBlue\Xmlify\Concerns\HandlesXmlify;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataTargets\AsXmlAttribute;
use NorseBlue\Xmlify\DataTargets\AsXmlCDataElement;
use NorseBlue\Xmlify\DataTargets\AsXmlElement;
use NorseBlue\Xmlify\DataTargets\AsXmlRoot;

#[AsXmlRoot(name: 'Book')]
class Book implements Xmlifiable
{
    use HandlesXmlify;

    public function __construct(
        #[AsXmlAttribute]
        public int $id,
        #[AsXmlElement]
        public string $name = '',
        #[AsXmlElement]
        public string $author = '',
        #[AsXmlElement]
        public string $genre = '',
        #[AsXmlElement]
        public array|Publication|null $publication = null,
        #[AsXmlCDataElement]
        public string $summary = '',
        #[AsXmlElement]
        public array $urls = [],
    ) {
        if (is_array($publication)) {
            $this->publication = new Publication(...$publication);
        }
    }

    #[AsXmlAttribute(name: 'slug')]
    public function getSlug()
    {
        return str($this->name)->slug();
    }
}
