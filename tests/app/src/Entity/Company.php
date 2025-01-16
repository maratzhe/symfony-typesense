<?php
declare(strict_types=1);


namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;



#[Entity]
class Company
{
    public function __construct(
        #[Id]
        #[Column]
        public int $id,

        #[Column]
        public string $name,

    )
    {

    }
}
