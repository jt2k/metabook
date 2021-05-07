<?php

namespace Metabook\DataSource;

use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use DateTime;

abstract class AbstractDataSource
{
    protected string $isbn;
    protected HttpClientInterface $client;

    public function __construct(string $isbn, StoreInterface $cache = null)
    {
        $this->isbn = $isbn;
        $this->client = HttpClient::create();
        if ($cache !== null) {
            $this->client = new CachingHttpClient($this->client, $cache, ['default_ttl' => 3600]);
        }
    }

    abstract public function lookup(): void;

    abstract public function getTitle(): ?string;

    abstract public function getSubtitle(): ?string;

    abstract public function getAuthor(): ?string;

    /**
     * @return string[]
     */
    abstract public function getAuthors(): array;

    abstract public function getPublisher(): ?string;

    /**
     * @return string[]
     */
    abstract public function getPublishers(): array;

    abstract public function getPublishedDate(): ?DateTime;

    abstract public function getCoverUrl(): ?string;

    abstract public function getIsbn10(): ?string;

    abstract public function getIsbn13(): ?string;
}
