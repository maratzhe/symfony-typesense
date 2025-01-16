<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Attribute;

use Attribute as BaseAttribute;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;

#[BaseAttribute(BaseAttribute::TARGET_PROPERTY | BaseAttribute::IS_REPEATABLE)]
class SearchRelation
{
    public function __construct(
        public SyncMode $sync = SyncMode::NONE,
        public bool $bulk = false
    ) {
    }
}
