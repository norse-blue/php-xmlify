<?php

declare(strict_types=1);

use NorseBlue\Xmlify\Tests\Types\Book;

it('converts a book into XML using ToXml() function', function ($id, $name, $author, $genre, $publication, $summary, $urls) {
    $book = new Book($id, $name, $author, $genre, $publication, $summary, $urls);
    $xml = file_get_contents("./tests/Fixtures/book_{$book->getSlug()}.xml");

    $this->assertEquals($xml, $book->ToXml());
})->with('books_data');

it('converts a book into XML by casting into string', function ($id, $name, $author, $genre, $publication, $summary, $urls) {
    $book = new Book($id, $name, $author, $genre, $publication, $summary, $urls);
    $xml = file_get_contents("./tests/Fixtures/book_{$book->getSlug()}.xml");

    $this->assertEquals($xml, (string) $book);
})->with('books_data');
