<?php

namespace App\Service\Search;

interface Indexable
{
    public function getObjectID(): string;

    public function getIndexName(): string;

    public function normalize(): array;
}