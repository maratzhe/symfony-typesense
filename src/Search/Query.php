<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

use Maratzhe\SymfonyTypesense\Service\Finder;

/**
 * @template T of object
 */
class Query
{
    /**
     * @var array<string, mixed> $searchParameters
     */
    protected array $searchParameters;

    //    public function __construct(string $q = null, string $queryBy = null)
    //    {
    //        $this->searchParameters = [];
    //        if ($q !== null) {
    //            $this->addParameter('q', $q);
    //        }
    //        if ($queryBy !== null) {
    //            $this->addParameter('query_by', $queryBy);
    //        }
    //
    //        return $this;
    //    }

    public function __construct(
        /**
         * @var Finder<T> $finder
         */
        protected Finder $finder,
    ) {
        $this->addParameter('enable_highlight_v1', false);
    }

    /**
     * @return Result<T>
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function getResult(): Result
    {
        return $this->finder->execute($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->searchParameters;
    }

    public function hasParameter(string $key): bool
    {
        return isset($this->searchParameters[$key]);
    }

    /**
     * @param string $q
     * @return Query<T>
     */
    public function q(string $q): self
    {
        return $this->addParameter('q', $q);
    }

    /**
     * @param string $queryBy
     * @return Query<T>
     */
    public function queryBy(string $queryBy): self
    {
        return $this->addParameter('query_by', $queryBy);
    }

    /**
     * @param int $maxHits
     * @return Query<T>
     */
    public function maxHits(int $maxHits): self
    {
        return $this->addParameter('max_hits', $maxHits);
    }


    /**
     * @param bool $prefix
     * @return Query<T>
     */
    public function prefix(bool $prefix): self
    {
        return $this->addParameter('prefix', $prefix);
    }


    /**
     * @param string $filterBy
     * @return Query<T>
     */
    public function filterBy(string $filterBy): self
    {
        return $this->addParameter('filter_by', $filterBy);
    }


    /**
     * @param string $sortBy
     * @return Query<T>
     */
    public function sortBy(string $sortBy): self
    {
        return $this->addParameter('sort_by', $sortBy);
    }


    /**
     * @param string $infix
     * @return Query<T>
     */
    public function infix(string $infix): self
    {
        return $this->addParameter('infix', $infix);
    }


    /**
     * @param string $facetBy
     * @return Query<T>
     */
    public function facetBy(string $facetBy): self
    {
        return $this->addParameter('facet_by', $facetBy);
    }


    /**
     * @param int $maxFacetValues
     * @return Query<T>
     */
    public function maxFacetValues(int $maxFacetValues): self
    {
        return $this->addParameter('max_facet_values', $maxFacetValues);
    }


    /**
     * @param string $facetQuery
     * @return Query<T>
     */
    public function facetQuery(string $facetQuery): self
    {
        return $this->addParameter('facet_query', $facetQuery);
    }

    /**
     * @param int $numTypos
     * @return Query<T>
     */
    public function numTypos(int $numTypos): self
    {
        return $this->addParameter('num_typos', $numTypos);
    }


    /**
     * @param int $page
     * @return Query<T>
     */
    public function page(int $page): self
    {
        return $this->addParameter('page', $page);
    }


    /**
     * @param int $perPage
     * @return Query<T>
     */
    public function perPage(int $perPage): self
    {
        return $this->addParameter('per_page', $perPage);
    }


    /**
     * @param int $groupLimit
     * @return Query<T>
     */
    public function groupLimit(int $groupLimit): self
    {
        return $this->addParameter('group_limit', $groupLimit);
    }


    /**
     * @param string $groupBy
     * @return Query<T>
     */
    public function groupBy(string $groupBy): self
    {
        return $this->addParameter('group_by', $groupBy);
    }

    /**
     * @param string $includeFields
     * @return Query<T>
     */
    public function includeFields(string $includeFields): self
    {
        return $this->addParameter('include_fields', $includeFields);
    }

    /**
     * @param string $excludeFields
     * @return Query<T>
     */
    public function excludeFields(string $excludeFields): self
    {
        return $this->addParameter('exclude_fields', $excludeFields);
    }


    /**
     * @param string $highlightFullFields
     * @return Query<T>
     */
    public function highlightFullFields(string $highlightFullFields): self
    {
        return $this->addParameter('highlight_full_fields', $highlightFullFields);
    }


    /**
     * @param int $snippetThreshold
     * @return Query<T>
     */
    public function snippetThreshold(int $snippetThreshold): self
    {
        return $this->addParameter('snippet_threshold', $snippetThreshold);
    }


    /**
     * @param int $dropTokensThreshold
     * @return Query<T>
     */
    public function dropTokensThreshold(int $dropTokensThreshold): self
    {
        return $this->addParameter('drop_tokens_threshold', $dropTokensThreshold);
    }


    /**
     * @param int $typoTokensThreshold
     * @return Query<T>
     */
    public function typoTokensThreshold(int $typoTokensThreshold): self
    {
        return $this->addParameter('typo_tokens_threshold', $typoTokensThreshold);
    }


    /**
     * @param string $pinnedHits
     * @return Query<T>
     */
    public function pinnedHits(string $pinnedHits): self
    {
        return $this->addParameter('pinned_hits', $pinnedHits);
    }

    /**
     * @param string $hiddenHits
     * @return Query<T>
     */
    public function hiddenHits(string $hiddenHits): self
    {
        return $this->addParameter('hidden_hits', $hiddenHits);
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return Query<T>
     */
    public function addParameter(string $key, mixed $value): self
    {
        $this->searchParameters[$key] = $value;

        return $this;
    }
}
