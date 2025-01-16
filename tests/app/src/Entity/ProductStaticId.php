<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\Color;
use App\Value\Pattern;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\FieldType;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
#[SearchCollection(sync: SyncMode::AUTO)]
class ProductStaticId
{
    #[Id]
    #[Column]
    #[SearchField]
    public ?int $id = null;


    /** @var array<int, Color>  */
    #[Column(type: 'color_array')]
    #[SearchField(name: 'colors.*', type: FieldType::INT32_ARRAY)]
    public array $colors;

    #[Column(nullable: true)]
    #[SearchField]
    public ?Pattern $pattern;


    /** @var Collection<int, CompositionStaticId> $compositions  */
    #[OneToMany(targetEntity: CompositionStaticId::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::AUTO)]
    public Collection $compositions;


    /**
     * @param int $id
     * @param array<int, Color> $colors
     * @param Pattern|null $pattern
     * @param array<int, CompositionStaticId> $compositions
     */
    public function __construct(
        int $id,
        array $colors = [],
        ?Pattern $pattern = null,
        array $compositions = [],
    )
    {
        $this->id = $id;
        $this->colors           = $colors;
        $this->compositions     = new ArrayCollection($compositions);
        $this->pattern          = $pattern;

        foreach ($this->compositions as $composition) {
            $composition->product = $this;
        }
    }
}
