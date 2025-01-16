<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;


#[Entity]
class MaterialNonSync
{

    /** @var Collection<int, CompositionNonSync> $compositions  */
    #[OneToMany(targetEntity: CompositionNonSync::class, mappedBy: 'material', cascade: ['all'], orphanRemoval: true)]
    public Collection $compositions;

    public function __construct(
        #[Id]
        #[Column]
        #[SearchField]
        public int $id,

        #[Column]
        #[SearchField]
        public string $name,
    )
    {
        $this->compositions = new ArrayCollection();
    }
}
