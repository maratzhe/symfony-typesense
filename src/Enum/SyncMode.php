<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Enum;

enum SyncMode: string
{
    case NONE = 'none';
    case AUTO = 'auto';
}
