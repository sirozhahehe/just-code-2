<?php

namespace App\Service\Search;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use App\Exception\IndexNotFoundException;
use App\Exception\NoObjectIdException;

class AlgoliaIndexingService implements IndexingInterface
{
    /** @var string[] */
    private array $indexNames = [];

    /** @var array<string, SearchIndex> */
    private array $indexes = [];

    /** @var array<string, array>  */
    private array $objectsToAdd = [];

    /** @var array<string, array>  */
    private array $objectsToRemove = [];

    /** @var array<string, array>  */
    private array $objectsToUpdate = [];


    public function __construct(
        private SearchClient $searchClient,
        array $configuration,
    ) {
        $this->boot($configuration);
    }

    public function persist(Indexable $object): void
    {
        $this->queueObjectToIndexing($object, $this->objectsToAdd);
    }

    public function remove(Indexable $object): void
    {
        $this->queueObjectToIndexing($object, $this->objectsToRemove);
    }

    public function partialUpdate(Indexable $object): void
    {
        $this->queueObjectToIndexing($object, $this->objectsToUpdate);
    }

    private function queueObjectToIndexing(Indexable $object, array &$queue): void
    {
        $this->assertIsIndexable($object);
        $queue[$object->getIndexName()][] = $object->normalize();
    }

    public function flush(): void
    {
        foreach ($this->objectsToAdd as $indexName => $objects) {
            $this->indexes[$indexName]->saveObjects($objects);
        }

        foreach ($this->objectsToUpdate as $indexName => $objects) {
            $this->indexes[$indexName]->partialUpdateObjects($objects);
        }

        foreach ($this->objectsToRemove as $indexName => $objects) {
            $this->indexes[$indexName]->deleteObjects($objects);
        }
    }

    public function clear(): void
    {
        $this->objectsToUpdate = $this->objectsToAdd = $this->objectsToRemove = [];
    }

    private function assertIsIndexable(Indexable $object)
    {
        match (true) {
            !$object->getObjectID() => throw new NoObjectIdException(),
            !$this->indexExists($object) => throw new IndexNotFoundException(),
            default => true,
        };
    }

    private function indexExists(Indexable $object): bool
    {
        return in_array($object->getIndexName(), $this->indexNames);
    }

    private function boot(array $configuration): void
    {
        foreach ($configuration['indices'] as $index) {
            $this->indexNames[] = $index['name'];
        }

        $prefix = $configuration['prefix'] ?? '';
        foreach ($this->indexNames as $indexName) {
            $this->indexes[$indexName] = $this->searchClient->initIndex($prefix . $indexName);
        }
    }
}