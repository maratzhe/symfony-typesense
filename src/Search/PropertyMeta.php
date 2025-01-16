<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;



use Maratzhe\SymfonyTypesense\Attribute\SearchField;

readonly class PropertyMeta
{
    public function __construct(
        /** @var class-string */
        public string  $class,

        public string  $name,

        public ?string $type,

        public bool    $is_embedded,

        /** @var class-string */
        public ?string $embeddedClass,

        /** @var array<int, FieldMapping> */
        public array   $mapping = []
    ) {
    }
}
