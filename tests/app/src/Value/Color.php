<?php
declare(strict_types=1);


namespace App\Value;

enum Color: int
{
    case White = 0;
    case Black = 1;

    case Red = 2;
    case Orange = 3;
    case Blue = 4;
}
