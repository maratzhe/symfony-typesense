<?php
declare(strict_types=1);

namespace App\Entity;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
class CompositionStaticId
{
    #[Id]
    #[Column]
    #[SearchField]
    public ?int $id = null;


    #[ManyToOne(targetEntity: ProductStaticId::class, cascade: ['persist'], inversedBy: 'compositions')]
    public ?ProductStaticId $product = null;

    public function __construct(
        #[ManyToOne(targetEntity: MaterialStaticId::class, cascade: ['persist'],  inversedBy: 'compositions')]
        #[SearchRelation(sync: SyncMode::AUTO)]
        public MaterialStaticId $material,

        #[Column]
        #[SearchField]
        public int $value
    )
    {
        $this->material->compositions->add($this);
    }
}
