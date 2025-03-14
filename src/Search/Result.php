<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

use Maratzhe\SymfonyTypesense\Service\Transformer;

/**
 * @template T of object
 */
class Result
{
    /**
     * @var array<int, array{field_name: string, sampled: bool, stats: array{total_values: int}, counts: array<int, array{count: int, highlighted:string, value:string|int|float}>  }>
     */
    public array $facet_counts;
    public int $found;

    /**
     * @var Hit<T>[]
     */
    public array $hits;
    public int $page;

    public int $pages;

    public int $per_page;

    public int $out_of;
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



        $this->facet_counts = $result['facet_counts'];
        $this->found = (int)($result['found'] ?? null);
        $this->hits = $hits;
        $this->page = (int)($result['page'] ?? null);
        $this->out_of = $result['out_of'];
        $this->search_time_ms = (int)($result['search_time_ms'] ?? null);

        $pages      = 0;
        $per_page   = isset($result['request_params']['per_page']) && is_scalar($result['request_params']['per_page'])
            ? (int) $result['request_params']['per_page'] : 0;

        if($per_page > 0) {
            $pages     = (int)($this->found / $per_page);
            $pages     += $this->found % $per_page > 0 ? 1 : 0;
        }

        $this->pages = $pages;
        $this->per_page = $per_page;
    }
}
