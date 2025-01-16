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
class CompositionPartial
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    public ?int $id;

    #[ManyToOne(targetEntity: ProductPartial::class, cascade: ['persist'], inversedBy: 'compositions')]
    public ?ProductPartial $product = null;

    public function __construct(

        #[ManyToOne(targetEntity: MaterialPartial::class,  inversedBy: 'compositions')]
        #[SearchRelation(sync: SyncMode::AUTO)]
        public MaterialPartial $material,

        #[Column]
        public int $value
    )
    {
        $this->material->compositions->add($this);
    }
}
