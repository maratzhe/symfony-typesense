<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;


readonly class ClassMeta
{
    public function __construct(

        /** @var class-string */
        public string           $class,

        public bool             $is_collection,

        public bool             $is_embedded,

        /** @var array<string, PropertyMeta> */
        public array            $fields,

        /** @var array<string, RelationMeta> */
        public array            $relations,

        public ?CollectionMapping $collection
    ) {
    }
}
