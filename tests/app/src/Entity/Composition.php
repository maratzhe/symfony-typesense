<?php
declare(strict_types=1);

namespace App\Entity;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\GeneratedValue;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
class Composition
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    #[SearchField]
    public ?int $id = null;


    #[ManyToOne(targetEntity: Product::class, cascade: ['persist'], inversedBy: 'compositions')]
    public ?Product $product = null;

    public function __construct(
        #[ManyToOne(targetEntity: Material::class, cascade: ['persist'],  inversedBy: 'compositions')]
        #[SearchRelation(sync: SyncMode::AUTO, bulk: false)]
        public Material $material,

        #[Column]
        #[SearchField]
        public int $value
    )
    {
        $this->material->compositions->add($this);
    }
}
