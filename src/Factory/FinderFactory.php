<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Factory;

use Maratzhe\SymfonyTypesense\Service\Finder;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Typesense\Client;


class FinderFactory
{
    /**
     * @template T of object
     * @param class-string<T> $class
     * @return Finder<T>
     */
    public function create(string $class): Finder
    {
        return new Finder($class, $this->client, $this->transformer, $this->mapper);
    }

    public function __construct(
        protected Client $client,
        protected Transformer $transformer,
        protected Mapper $mapper,
    ) {
    }
}
