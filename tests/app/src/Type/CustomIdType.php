<?php
declare(strict_types=1);

namespace App\Type;



use App\Value\CustomId;
use Symfony\Bridge\Doctrine\Types\AbstractUidType;


class CustomIdType extends AbstractUidType
{

    protected function getUidClass() : string
    {
        return CustomId::class;
    }

    public function getName() : string
    {
        return 'custom_id';
    }
}
