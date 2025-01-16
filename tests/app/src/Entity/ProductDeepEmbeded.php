<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\EmbededPrice;
use App\Value\EmbededPrice2;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
#[SearchCollection(sync: SyncMode::AUTO)]
class ProductDeepEmbeded
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    #[SearchField]
    public ?int $id;

    public function __construct(
        #[Embedded(class: EmbededPrice2::class)]
        public ?EmbededPrice2 $emb_price2
    )
    {

    }
}
