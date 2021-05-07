<?php

use Metabook\Book;
use Symfony\Component\HttpKernel\HttpCache\Store;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    echo "Please specify ISBN\n";
    exit(1);
}
$isbn = $argv[1];

$cache = new Store(__DIR__ . '/cache');
$book = new Book($isbn, $cache);
$book->lookup();

echo json_encode([
    'isbn10' => $book->getIsbn10(),
    'isbn13' => $book->getIsbn13(),
    'author' => $book->getAuthor(),
    'title' => $book->getTitle(),
    'subtitle' => $book->getSubtitle(),
    'publisher' => $book->getPublisher(),
    'publishedDate' => $book->getPublishedDate() ? $book->getPublishedDate()->format('Y-m-d') : null,
    'cover' => $book->getCoverUrl(),
    'pages' => $book->getNumberOfPages()
], JSON_PRETTY_PRINT);
echo "\n";
