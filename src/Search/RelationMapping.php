<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

use Maratzhe\SymfonyTypesense\Enum\SyncMode;


readonly class RelationMapping
{
    public function __construct(
        public SyncMode $sync,
        public bool $bulk
    ) {
    }
}
