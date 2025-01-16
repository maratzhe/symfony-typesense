<?php

declare(strict_types=1);

namespace App\Value;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Embedded;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;


#[Embeddable]
class EmbededPrice
{
    public function __construct(

        #[Column(nullable: true)]
        #[SearchField]
        public ?string $name,

        #[Embedded(class: Price::class)]
        public ?Price $price_value
    )
    {

    }
}
