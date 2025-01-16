<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
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
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\FieldType;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


#[Entity]
#[SearchCollection(sync: SyncMode::AUTO)]
class Product
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    #[SearchField]
    public ?int $id = null;

    #[Column(type: 'custom_id', nullable: true)]
    #[SearchField]
    public ?CustomId $custom_id;

    /** @var array<int, Color>  */
    #[Column(type: 'color_array')]
    #[SearchField(name: 'colors.*', type: FieldType::INT32_ARRAY)]
    public array $colors;

    /** @var array<int, Photo>  */
    #[Column(type: 'photo_array')]
    #[SearchField(name: 'photos.*', type: FieldType::OBJECT_ARRAY)]
    #[SearchField(name: 'photos.*.size', type: FieldType::INT32)]
    #[SearchField(name: 'photos.*.url', type: FieldType::STRING)]
    public array $photos;

    #[Column(nullable: true)]
    #[SearchField]
    public ?Pattern $pattern;

    #[Embedded(class: Price::class)]
    public ?Price $price;

    /** @var Collection<int, Composition> $compositions  */
    #[OneToMany(targetEntity: Composition::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::AUTO, bulk: true)]
    public Collection $compositions;

    #[OneToOne(targetEntity: Properties::class, cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::AUTO, bulk: true)]
    public ?Properties $properties;

    /**
     * @param CustomId|null $custom_id
     * @param array<int, Color> $colors
     * @param array<int, Photo> $photos
     * @param Pattern|null $pattern
     * @param Price|null $price
     * @param array<int, Composition> $compositions
     * @param Properties|null $properties
     */
    public function __construct(
        ?CustomId $custom_id = null,
        array $colors = [],
        array $photos = [],
        ?Pattern $pattern = null,
        ?Price $price = null,
        array $compositions = [],
        ?Properties $properties = null
    )
    {
        $this->custom_id        = $custom_id;
        $this->colors           = $colors;
        $this->photos           = $photos;
        $this->compositions     = new ArrayCollection($compositions);
        $this->pattern          = $pattern;
        $this->price            = $price;
        $this->properties       = $properties;

        foreach ($this->compositions as $composition) {
            $composition->product = $this;
        }
    }
}
