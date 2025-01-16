<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;


#[Entity]
class MaterialStaticId
{
    #[Id]
    #[Column]
    #[SearchField]
    public ?int $id = null;

    /** @var Collection<int, CompositionStaticId> $compositions */
    #[OneToMany(targetEntity: CompositionStaticId::class, mappedBy: 'material', cascade: ['all'], orphanRemoval: true)]
    public Collection $compositions;

    public function __construct(
        #[Column]
        #[SearchField]
        public string $name,
    )
    {
        $this->compositions = new ArrayCollection();
    }
}
