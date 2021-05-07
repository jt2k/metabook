<?php

namespace Metabook;

use Metabook\DataSource\AbstractDataSource;
use Metabook\DataSource\GoogleBooks;
use Metabook\DataSource\OpenLibrary;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use DateTime;

class Book
{
    private string $isbn;
    private StoreInterface $cache;
    /** @var AbstractDataSource[] */
    private array $dataSources = [];

    public function __construct(string $isbn, StoreInterface $cache = null)
    {
        $this->isbn = $isbn;
        $this->cache = $cache;
    }

    public function lookup(): void
    {
        $googleBooks = new GoogleBooks($this->isbn, $this->cache);
        $googleBooks->lookup();
        $this->dataSources['GoogleBooks'] = $googleBooks;

        $openLibrary = new OpenLibrary($this->isbn, $this->cache);
        $openLibrary->lookup();
        $this->dataSources['OpenLibrary'] = $openLibrary;
    }

    /**
     * @return mixed
     */
    private function getFirst(string $method)
    {
        foreach ($this->dataSources as $source) {
            $value = $source->{$method}();
            if (!empty($value)) {
                return $value;
            }
        }
    }

    /**
     * @param string[] $order
     * @return mixed
     */
    private function getPreference(string $method, array $order)
    {
        foreach ($order as $sourceType) {
            $source = $this->dataSources[$sourceType];
            $value = $source->{$method}();
            if (!empty($value)) {
                return $value;
            }
        }

        return $this->getFirst($method);
    }

    public function getTitle(): ?string
    {
        return $this->getFirst('getTitle');
    }

    public function getSubtitle(): ?string
    {
        return $this->getFirst('getSubtitle');
    }

    public function getAuthor(): ?string
    {
        return $this->getFirst('getAuthor');
    }

    /**
     * @return string[]
     */
    public function getAuthors(): array
    {
        return $this->getFirst('getAuthors');
    }

    public function getPublisher(): ?string
    {
        return $this->getFirst('getPublisher');
    }

    /**
     * @return string[]
     */
    public function getPublishers(): array
    {
        return $this->getFirst('getPublishers');
    }

    public function getPublishedDate(): ?DateTime
    {
        return $this->getFirst('getPublishedDate');
    }

    public function getCoverUrl(): ?string
    {
        return $this->getPreference('getCoverUrl', ['OpenLibrary']);
    }

    public function getNumberOfPages(): ?int
    {
        return $this->getFirst('getNumberOfPages');
    }

    public function getIsbn10(): ?string
    {
        return $this->getFirst('getIsbn10');
    }

    public function getIsbn13(): ?string
    {
        return $this->getFirst('getIsbn13');
    }
}
