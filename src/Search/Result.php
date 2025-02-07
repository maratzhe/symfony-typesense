<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

use Maratzhe\SymfonyTypesense\Service\Transformer;

/**
 * @template T of object
 */
class Result
{
    public ?int $facet_counts;
    public int $found;

    /**
     * @var Hit<T>[]
     */
    public array $hits;
    public int $page;
    public int $search_time_ms;

    /**
     * @param Transformer $transformer
     * @param class-string<T> $class
     * @param \TypesenseResult $result
     * @throws \Exception
     */
    public function __construct(Transformer $transformer, string $class, array $result)
    {
        $hits = [];
        foreach ($result['hits'] as $hit) {
            $hits[] = new Hit(
                $transformer->hydrate($class, $hit['document']),
                $hit['highlight'],
                $hit['text_match'] ?? null,
                $hit['text_match_info'] ?? null,
            );
        }

//        $this->facet_counts = $result['facetCounts'] ?? null;
        $this->facet_counts = 0;
        $this->found = (int)($result['found'] ?? null);
        $this->hits = $hits;
        $this->page = (int)($result['page'] ?? null);
        $this->search_time_ms = (int)($result['search_time_ms'] ?? null);
    }
}
