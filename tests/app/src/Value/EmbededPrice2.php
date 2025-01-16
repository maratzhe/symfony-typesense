<?php

declare(strict_types=1);

namespace App\Value;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Embedded;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;


#[Embeddable]
class EmbededPrice2
{
    public function __construct(

        #[Column(nullable: true)]
        #[SearchField]
        public ?string $type,

        #[Embedded(class: EmbededPrice::class)]
        public ?EmbededPrice $emb_price
    )
    {

    }
}
