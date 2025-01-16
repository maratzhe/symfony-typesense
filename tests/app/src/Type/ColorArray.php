<?php
declare(strict_types=1);

namespace App\Type;

use App\Value\Color;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;


class ColorArray extends JsonType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform) : string
    {
        if (!is_array($value)) {
            throw ConversionException::conversionFailedSerialization($value, 'json', 'wrong argument');
        }

        return parent::convertToDatabaseValue($value, $platform);
    }


    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return array<int, Color>
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform) : array
    {
        /** @var array<int, int> $array */
        $array  = parent::convertToPHPValue($value, $platform);

        return array_map(function ($item) {
            return Color::from($item);
        }, $array);
    }
}
