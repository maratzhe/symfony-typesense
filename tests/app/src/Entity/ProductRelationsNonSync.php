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
use Doctrine\ORM\Mapping\OneToOne;
use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
#[SearchCollection(sync: SyncMode::AUTO)]
class ProductRelationsNonSync
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    public ?int $id;

    /** @var Collection<int, CompositionNonSync> $compositions  */
    #[OneToMany(targetEntity: CompositionNonSync::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::NONE)]
    public Collection $compositions;

    #[OneToOne(targetEntity: Properties::class, cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::NONE)]
    public ?Properties $properties;

    /**
     * @param array<int, CompositionNonSync> $compositions
     * @param Properties|null $properties
     */
    public function __construct(
        array $compositions = [],
        ?Properties $properties = null
    )
    {
        $this->compositions     = new ArrayCollection($compositions);
        $this->properties       = $properties;

        foreach ($this->compositions as $composition) {
            $composition->product = $this;
        }
    }
}
