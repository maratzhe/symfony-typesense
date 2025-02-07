<?php
declare(strict_types=1);

namespace App\Type;


use App\Value\Photo;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;



/**
 *
 *
 */
class PhotoArray extends JsonType
{
//    /**
//     * @template T
//     * @param T $value
//     * @param AbstractPlatform $platform
//     * @return string
//     * @throws ConversionException
//     */

    /**
     * @template T
     * @param T $value
     * @param AbstractPlatform $platform
     * @return string
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform) : string
    {
        assert(is_array($value));
        $data   = array_map(function (mixed $item) {
            /** @var Photo $item */
            return ['size' => $item->size, 'url' => $item->url];
        } , $value);

        return parent::convertToDatabaseValue($data, $platform);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return array<int, Photo>
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform) : array
    {
        /** @var array<int, array{size:int, url:string}> $array */
        $array  = parent::convertToPHPValue($value, $platform);

        return array_map(function ($item) {
            return new Photo($item['size'], $item['url']);
        }, $array);
    }
}
