<?php

declare(strict_types=1);

namespace App\Entity;

use App\Value\Pattern;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;


#[Entity]
#[SearchCollection]
class ProductNonSync
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    #[SearchField]
    public ?int $id;

    public function __construct(
        #[Column(nullable: true)]
        #[SearchField]
        public Pattern $pattern
    )
    {

    }
}
