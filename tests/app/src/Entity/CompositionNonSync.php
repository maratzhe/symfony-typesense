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
class CompositionNonSync
{

    #[ManyToOne(targetEntity: ProductRelationsNonSync::class, cascade: ['persist'], inversedBy: 'compositions')]
    public ?ProductRelationsNonSync $product = null;

    public function __construct(
        #[Id]
        #[Column]
        #[GeneratedValue]
        #[SearchField]
        public ?int $id,

        #[ManyToOne(targetEntity: MaterialNonSync::class,  inversedBy: 'compositions')]
        #[SearchRelation(sync: SyncMode::NONE)]
        public MaterialNonSync $material,

        #[Column]
        #[SearchField]
        public int $value
    )
    {
        $this->material->compositions->add($this);
    }
}
