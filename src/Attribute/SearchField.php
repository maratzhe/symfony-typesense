<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Attribute;

use Attribute as BaseAttribute;
use Maratzhe\SymfonyTypesense\Enum\FieldType;
use Closure;

#[BaseAttribute(BaseAttribute::TARGET_PROPERTY | BaseAttribute::IS_REPEATABLE)]
class SearchField
{
    public function __construct(
        public ?string $name = null,
        public ?FieldType $type = null,
        public string $locale = '',
        public bool $optional = true,
        public bool $facet = false,
        public bool $index = false,
        public bool $infix = false,
        public bool $sort = false,
        public bool $stem = false
    ) {
    }
}
