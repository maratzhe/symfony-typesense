<?php
declare(strict_types=1);

namespace App\Value;





readonly class Photo
{
    public function __construct(
        public int $size,
        public string $url,
    )
    {

    }

}
