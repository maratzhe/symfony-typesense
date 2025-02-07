<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Attribute;

use Attribute as BaseAttribute;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;

#[BaseAttribute(BaseAttribute::TARGET_CLASS)]
class SearchCollection
{
    public function __construct(
        public ?string $name = null,
        public SyncMode $sync = SyncMode::NONE,
    ) {
    }
}
