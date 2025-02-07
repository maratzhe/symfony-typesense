<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Enum;

enum FieldType: string
{
    case STRING = 'string';
    case STRING_ARRAY = 'string[]';
    case STRING_ASTERISK = 'string*';

    case INT32 = 'int32';

    case INT32_ARRAY = 'int32[]';

    case INT64 = 'int64';

    case INT64_ARRAY = 'int64[]';

    case FLOAT = 'float';

    case FLOAT_ARRAY = 'float[]';

    case BOOL = 'bool';

    case BOOL_ARRAY = 'bool[]';

    case GEOPOINT = 'geopoint';

    case GEOPOINT_ARRAY = 'geopoint[]';

    case OBJECT = 'object';

    case OBJECT_ARRAY = 'object[]';

    case AUTO = 'auto';

    public static function fromDoctrineType(string $type): self
    {
        return match ($type) {
            'int',
            'integer' => self::INT32,
            'json_document' => self::OBJECT_ARRAY,
            'json' => self::STRING_ARRAY,
            'double precision' => self::FLOAT,
            'boolean'   => self::BOOL,
            default => self::STRING,
        };
    }
}
