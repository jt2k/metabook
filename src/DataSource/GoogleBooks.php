<?php

namespace Metabook\DataSource;

use DateTime;

class GoogleBooks extends AbstractDataSource
{
    private object $responseObject;

    public function lookup(): void
    {
        $isbnSearch = "isbn:{$this->isbn}";
        $response = $this->client->request('GET', 'https://www.googleapis.com/books/v1/volumes', [
            'query' => [
                'q' => $isbnSearch
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            $responseObject = json_decode($response->getContent());
            if ($responseObject && isset($responseObject->items) && count($responseObject->items) > 0 && isset($responseObject->items[0]->volumeInfo)) {
                $this->responseObject = $responseObject->items[0]->volumeInfo;
            }
        }
    }

    public function getTitle(): ?string
    {
        return $this->responseObject->title ?? null;
    }

    public function getSubtitle(): ?string
    {
        return $this->responseObject->subtitle ?? null;
    }

    public function getAuthor(): ?string
    {
        $authors = $this->getAuthors();

        return $authors ? $authors[0] : null;
    }

    public function getAuthors(): array
    {
        $authors = [];
        if (isset($this->responseObject->authors)) {
            $authors = $this->responseObject->authors;
        }

        return $authors;
    }

    public function getPublisher(): ?string
    {
        return $this->responseObject->publisher ?? null;
    }

    public function getPublishers(): array
    {
        return $this->getPublisher() !== null ? [$this->getPublisher()] : [];
    }

    public function getPublishedDate(): ?DateTime
    {
        if (isset($this->responseObject->publishedDate)) {
            try {
                if (preg_match('/^\d{4}$/', $this->responseObject->publishedDate)) {
                    $date = new \DateTime($this->responseObject->publishedDate . '-01-01');
                } else {
                    $date = new \DateTime($this->responseObject->publishedDate);
                }

                return $date;
            } catch (\Exception $e) {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getCoverUrl(): ?string
    {
        return $this->responseObject->imageLinks->thumbnail ?? null;
    }

    public function getNumberOfPages(): ?int
    {
        return $this->responseObject->pageCount ?? null;
    }

    public function getIsbn10(): ?string
    {
        return $this->getIsbn(10);
    }

    public function getIsbn13(): ?string
    {
        return $this->getIsbn(13);
    }

    private function getIsbn(int $type): ?string
    {
        $identifierType = sprintf('ISBN_%d', $type);
        if (!isset($this->responseObject->industryIdentifiers) || !is_array($this->responseObject->industryIdentifiers)) {
            return null;
        }
        foreach ($this->responseObject->industryIdentifiers as $identifier) {
            if (isset($identifier->type) && isset($identifier->identifier) && ($identifier->type === $identifierType)) {
                return $identifier->identifier;
            }
        }

        return null;
    }
}
