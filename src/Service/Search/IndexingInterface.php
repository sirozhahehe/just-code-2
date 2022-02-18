<?php

namespace App\Service\Search;

interface IndexingInterface
{
    public function partialUpdate(Indexable $object): void;

    public function persist(Indexable $object): void;

    public function remove(Indexable $object): void;

    public function clear(): void;

    public function flush(): void;
}