<?php
declare(strict_types=1);

namespace App\Value;





use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;

#[Embeddable]
readonly class PricePartial
{
    public function __construct(
        #[Column(nullable: true)]
        #[SearchField]
        public ?int $price,

        #[Column(nullable: true)]
        public ?string $currency,
    )
    {

    }

}
