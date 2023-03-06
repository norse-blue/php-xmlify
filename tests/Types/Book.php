<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Tests\Types;

use NorseBlue\Xmlify\Concerns\HandlesXmlify;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataTargets\AsXmlAttribute;
use NorseBlue\Xmlify\DataTargets\AsXmlCDataElement;
use NorseBlue\Xmlify\DataTargets\AsXmlElement;
use NorseBlue\Xmlify\DataTargets\AsXmlRoot;

#[AsXmlRoot(name: 'Book', namespace: 'book', namespaces: ['book' => 'https://www.example.com/books.xsd'])]
class Book implements Xmlifiable
{
    use HandlesXmlify;

    /**
     * @param  int  $id
     * @param  string  $name
     * @param  string  $author
     * @param  string  $genre
     * @param  array<string, string>|\NorseBlue\Xmlify\Tests\Types\Publication|null  $publication
     * @param  array<string>  $urls
     */
    public function __construct(
        #[AsXmlAttribute]
        public int $id,
        #[AsXmlElement]
        public string $name = '',
        #[AsXmlElement]
        public string $author = '',
        #[AsXmlElement]
        public string $genre = '',
        #[AsXmlElement(name: 'GetsOverridenByObject')]
        public array|Publication|null $publication = null,
        #[AsXmlCDataElement]
        public string $summary = '',
        #[AsXmlElement(namespaces: ['urls-collection' => 'https://www.example.com/urls-collection.xsd'])]
        public array $urls = [],
    ) {
        if (is_array($publication)) {
            $this->publication = new Publication(...$publication);
        }
    }

    #[AsXmlAttribute(name: 'slug')]
    public function getSlug(): string
    {
        return str($this->name)->slug()->value();
    }
}
