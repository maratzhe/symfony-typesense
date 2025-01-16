<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\Color;
use App\Value\Pattern;
use App\Value\PricePartial;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
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
class ProductPartial
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    public ?int $id;


    /** @var array<int, Color>  */
    #[Column(type: 'color_array')]
    public array $colors;

    #[Column(nullable: true)]
    public ?Pattern $pattern;

    #[Embedded(class: PricePartial::class)]
    public ?PricePartial $price;


    /** @var Collection<int, CompositionPartial> $compositions  */
    #[OneToMany(targetEntity: CompositionPartial::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::AUTO)]
    public Collection $compositions;

    #[OneToOne(targetEntity: PropertiesPartial::class, cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::AUTO)]
    public ?PropertiesPartial $properties;

    /**
     * @param array<int, Color> $colors
     * @param Pattern|null $pattern
     * @param PricePartial|null $price
     * @param array<int, CompositionPartial> $compositions
     * @param PropertiesPartial|null $properties
     */
    public function __construct(
        array $colors = [],
        ?Pattern $pattern = null,
        ?PricePartial $price = null,
        array $compositions = [],
        ?PropertiesPartial $properties = null
    )
    {
        $this->colors           = $colors;
        $this->pattern          = $pattern;
        $this->price            = $price;;
        $this->compositions     = new ArrayCollection($compositions);
        $this->properties       = $properties;

        foreach ($this->compositions as $composition) {
            $composition->product = $this;
        }
    }
}
