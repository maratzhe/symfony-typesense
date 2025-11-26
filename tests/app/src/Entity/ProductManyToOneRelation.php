<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\Pattern;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;

use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
#[SearchCollection(sync: SyncMode::AUTO)]
class ProductManyToOneRelation
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    #[SearchField]
    public ?int $id = null;


    /**
     * @param ?Pattern $pattern
     * @param Company $company
     */
    public function __construct(
        #[Column(nullable: true)]
        #[SearchField]
        public ?Pattern $pattern,


        #[SearchRelation(sync: SyncMode::AUTO)]
        #[ManyToOne]
        public Company $company
    )
    {

    }
}
