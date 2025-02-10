<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;

use Maratzhe\SymfonyTypesense\Search\Query;
use Maratzhe\SymfonyTypesense\Search\Result;
use Typesense\Client;


/**
 * @template T of object
 */
class Finder
{
    public function __construct(

        /**
         * @var class-string<T> $class
         */
        protected string $class,
        protected Client $client,
        protected Transformer $transformer,
        protected Mapper $mapper,
    ) {
    }

    /**
     * @param string|null $q
     * @param string|null $queryBy
     * @return Query<T>
     */
    public function query(?string $q = '*', ?string $queryBy = null): Query
    {
        $query = new Query($this);
        if (null !== $q) {
            $query->q($q);
        }

        if (null !== $queryBy) {
            $query->queryBy($queryBy);
        }

        return $query;
    }

    /**
     * @param Query<T> $query
     * @return Result<T>
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function execute(Query $query): Result
    {
        /**
         * @var \TypesenseResult $result
         */
        $result = $this->client->collections[$this->mapper->mapping($this->class)['name']]->documents->search($query->getParameters());


        return new Result($this->transformer, $this->class, $result);
    }

    /**
     * @param Query<T>[] $queries
     * @param Query<T>|null $common
     * @return Result<T>[]
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function multiSearch(array $queries, ?Query $common = null): array
    {

        $searches = [];
        foreach ($queries as $query) {
            $searches[] = [...$query->getParameters(), ...['collection' => $this->mapper->mapping($this->class)['name']]];
        }

        /**
         * @var array{results: \TypesenseResult[]} $results
         */
        $results    = $this->client->multiSearch->perform(
            [
                'searches' => $searches,
            ],
            $common !== null ? $common->getParameters() : []
        );

        $return     = [];
        foreach ($results['results'] as $result) {
            if(isset($result['error'])) {
                throw new \RuntimeException($result['error']);
            }

            $return[] = new Result($this->transformer, $this->class, $result);
        }

        return $return;
    }
}
