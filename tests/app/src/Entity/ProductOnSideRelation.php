<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\FieldType;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
#[SearchCollection(sync: SyncMode::AUTO)]
class ProductOnSideRelation
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

        #[ManyToOne(targetEntity: Company::class, inversedBy: 'products')]
//        #[SearchRelation]
        public Company $company
    )
    {

    }
}
