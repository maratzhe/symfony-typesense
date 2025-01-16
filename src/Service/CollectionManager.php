<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;

use Typesense\Client;
use Typesense\Collection as TypesenseCollection;
use Exception;

class CollectionManager
{
    public function __construct(
        protected Client $client,
        protected Mapper $mapper,
    ) {

    }

    /**
     * @param string $class
     * @return array<string, mixed>
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function delete(string $class): array
    {
        /** @var array<string, mixed>  $result */
        $result = $this->client->collections[$this->mapper->mapping($class)['name']]->delete();

        return $result;
    }

    public function exists(string $class): bool
    {
        return (bool)$this->client->collections[$this->mapper->mapping($class)['name']]->exists();
    }

    public function get(string $class): TypesenseCollection
    {
        return $this->client->collections[$this->mapper->mapping($class)['name']];
    }

    /**
     * @param string $name
     * @return array<string, mixed>
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function create(string $name): array
    {
        $mapping    = $this->mapper->mapping($name);

        try {
            /** @var array<string, mixed>  $result */
            $result = $this->client->collections->create($mapping);
        }
        catch (Exception $e) {
            throw new Exception('unable to create collection. '
                . "\n" . $e->getMessage() . "\n" . 'Config:' . json_encode($mapping)
            );
        }

        return $result;
    }
}
