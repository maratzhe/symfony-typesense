<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

use Maratzhe\SymfonyTypesense\Enum\SyncMode;


class CollectionMapping
{
    public function __construct(
        public string $name,

        public SyncMode $sync
    ) {
    }
}
