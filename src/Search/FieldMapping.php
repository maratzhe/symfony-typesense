<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;


use Maratzhe\SymfonyTypesense\Enum\FieldType;

readonly class FieldMapping
{
    public function __construct(
        public string $name,
        public ?FieldType $type,
        public string $locale,
        public bool $optional,
        public bool $facet,
        public bool $index,
        public bool $infix,
        public bool $sort,
        public bool $stem,
    ) {
    }
}
