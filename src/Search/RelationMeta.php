<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Search\FieldMapping as FieldMapping;
use Maratzhe\SymfonyTypesense\Search\RelationMapping as RelationMapping;


readonly class RelationMeta
{
    public function __construct(
        /** @var class-string */
        public string $class,

        public string $field,

        public ?string $reverse,

        public bool $child,

        public bool $to_one,

        public ?RelationMapping $relation
    ) {
    }
}
