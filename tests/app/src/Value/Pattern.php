<?php
declare(strict_types=1);

namespace App\Value;


enum Pattern : string
{
    case None           = 'none';
    case Floral         = 'floral';
    case Animal         = 'animal';
    case Camouflage     = 'camouflage';
}
