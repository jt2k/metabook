<?php

namespace Metabook\DataSource;

use DateTime;

class OpenLibrary extends AbstractDataSource
{
    private object $responseObject;

    public function lookup(): void
    {
        $isbnKey = "ISBN:{$this->isbn}";
        $response = $this->client->request('GET', 'https://openlibrary.org/api/books', [
            'query' => [
                'bibkeys' => $isbnKey,
                'jscmd' => 'data',
                'format' => 'json'
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            $responseObject = json_decode($response->getContent());
            if ($responseObject && is_object($responseObject) && isset($responseObject->$isbnKey)) {
                $this->responseObject = $responseObject->$isbnKey;
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
            foreach ($this->responseObject->authors as $author) {
                $authors[] = $author->name;
            }
        }

        return $authors;
    }

    public function getPublisher(): ?string
    {
        $publishers = $this->getPublishers();

        return $publishers ? $publishers[0] : null;
    }

    public function getPublishers(): array
    {
        $publishers = [];
        if (isset($this->responseObject->publishers)) {
            foreach ($this->responseObject->publishers as $publisher) {
                $publishers[] = $publisher->name;
            }
        }

        return $publishers;
    }

    public function getPublishedDate(): ?DateTime
    {
        if (isset($this->responseObject->publish_date)) {
            try {
                if (preg_match('/^\d{4}$/', $this->responseObject->publish_date)) {
                    $date = new \DateTime($this->responseObject->publish_date . '-01-01');
                } else {
                    $date = new \DateTime($this->responseObject->publish_date);
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
        return $this->responseObject->cover->large ?? null;
    }

    public function getNumberOfPages(): ?int
    {
        return $this->responseObject->number_of_pages ?? null;
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
        $identifierType = sprintf('isbn_%d', $type);

        return $this->responseObject->identifiers->$identifierType[0] ?? null;
    }
}
