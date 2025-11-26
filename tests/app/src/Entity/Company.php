<?php
declare(strict_types=1);


namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;


#[Entity]
class Company
{

    #[Id]
    #[Column]
    #[GeneratedValue]
    public ?int $id;

    public function __construct(

        #[Column]
        #[SearchField]
        public string $name,

    )
    {

    }
}
