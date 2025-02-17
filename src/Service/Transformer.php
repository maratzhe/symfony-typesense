<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;


class Transformer
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Mapper $mapper,
    ) {
    }

    public function id(object $entity): string
    {
        $meta = $this->entityManager->getClassMetadata($entity::class);
        $platform = $this->entityManager->getConnection()->getDatabasePlatform();

        $ids = array_map(function ($field) use ($meta, $entity, $platform) {
            $mapping = $meta->getFieldMapping($field);
            $type = Type::getType($mapping->type);
            $value = $meta->getFieldValue($entity, $field);

            return $type->convertToDatabaseValue($value, $platform);
        }, $meta->identifier);

        return implode('-', $ids);
    }

    /**
     * @template T of object
     * @param T $model
     * @return array<string, mixed>
     * @throws Exception
     * @throws ConversionException
     */
    public function normalize(object $model): array
    {
        $meta = $this->entityManager->getClassMetadata($model::class);
        if (count($meta->getIdentifierValues($model)) === 0) {
            return [];
        }

        $fields = $this->normalizeObject($model, '');

        if (isset($fields['id']) && is_scalar($fields['id'])) {
            $fields['id'] = (string) $fields['id'];
        } else {
            $fields['id']   = $this->id($model);
        }

        return $fields;
    }

    /**
     * @template T of object
     * @param T $model
     * @param string $prefix
     * @return array<string, mixed>
     * @throws Exception
     * @throws ConversionException
     */
    protected function normalizeObject(object $model, string $prefix): array
    {
        /** @var array<string, mixed> $fields */
        $fields         = [];
        $meta           = $this->entityManager->getClassMetadata($model::class);
        $mapperMeta     = $this->mapper->meta($model);
        $identifiers    = $meta->getIdentifierFieldNames();
        $platform       = $this->entityManager->getConnection()->getDatabasePlatform();
        $initialized    = !$this->entityManager->isUninitializedObject($model);

        if ($mapperMeta === null || $meta->reflClass === null) {
            return [];
        }

        if(!$mapperMeta->is_embedded && count($meta->getIdentifierValues($model)) === 0) {
            return [];
        }


        foreach ($mapperMeta->fields as $field) {
            if(!$initialized && !in_array($field->name, $identifiers, true)) {
                continue;
            }

            $value  = $meta->getFieldValue($model, $field->name);

            if($field->is_embedded && is_object($value)) {
                $localFields    = $this->normalizeObject($value, $prefix . $field->name . '.');
                $fields         = [...$fields, ...$localFields];
            }
            else {
                if($field->type === null)  {
                    continue;
                }

                $type   = Type::getType($field->type);
                /** @var string|null|int|float|bool $converted */
                $converted  = $type->convertToDatabaseValue($value, $platform);

                if($type instanceof JsonType) {
                    $converted  = json_decode((string)$converted, true);
                }

                $fields[$prefix.$field->name] = $converted;
            }
        }


        foreach ($mapperMeta->relations as $relation) {
            if(!$relation->child) {
                continue;
            }

            $value = $meta->getFieldValue($model, $relation->field);

            if ($value instanceof Collection) {
                $current = 0;
                foreach ($value as $fv) {
                    /** @var object $fv */
                    $localMeta = $this->entityManager->getClassMetadata($fv::class);
                    if (count($localMeta->getIdentifierValues($fv)) === 0) {
                        continue;
                    }

                    $localPref = $prefix.$relation->field.'.'.$current.'.';
                    $localFields = $this->normalizeObject($fv, $localPref);
                    $fields = [...$fields, ...$localFields];
                    ++$current;
                }
            } else {
                if(!is_object($value)) {
                    continue;
                }

                $localMeta = $this->entityManager->getClassMetadata($value::class);
                if (count($localMeta->getIdentifierValues($value)) === 0) {
                    continue;
                }

                $localPref = $prefix.$relation->field.'.';
                $localFields = $this->normalizeObject($value, $localPref);
                $fields = [...$fields, ...$localFields];
            }
        }

        return $fields;
    }


    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $fields
     * @return T
     * @throws ConversionException
     * @throws Exception
     */
    public function hydrate(string $class, array $fields): object
    {
        $meta   = $this->entityManager->getClassMetadata($class);
        /** @var T $model */
        $model  = $meta->newInstance();

        $this->hydrateObject($model, $fields, '');

        return $model;
    }

    /**
     * @param object $model
     * @param array<string, mixed> $fields
     * @param string $prefix
     * @return bool
     * @throws ConversionException
     * @throws Exception
     */
    protected function hydrateObject(object $model, array $fields, string $prefix): bool
    {
        $meta       = $this->entityManager->getClassMetadata($model::class);
        $classMeta  = $this->mapper->meta($model);
        if($classMeta === null) {
            return false;
        }


        $platform = $this->entityManager->getConnection()->getDatabasePlatform();
        $hydrated = false;



        foreach ($classMeta->fields as $field) {
            $key = $prefix.$field->name;


            if($field->is_embedded && $field->embeddedClass !== null) {
                $localMeta  = $this->entityManager->getClassMetadata($field->embeddedClass);
                $localModel = $localMeta->newInstance();

                if($this->hydrateObject($localModel, $fields, $prefix.$field->name . '.')) {
                    $hydrated = true;
                    $meta->setFieldValue($model, $field->name, $localModel);
                }
            }
            elseif($field->type !== null && isset($fields[$key])) {
                $type = Type::getType($field->type);
                $value = $fields[$key];
                if($type instanceof JsonType) {
                    $value  = json_encode($value);
                }

                $value = $type->convertToPHPValue($value, $platform);

                $meta->setFieldValue($model, $field->name, $value);
                $hydrated = true;
            }
        }

        if (!$hydrated) {
            return false;
        }

        foreach ($classMeta->relations as $relation) {
            if(!$relation->child) {
                continue;
            }


            if (!$relation->to_one) {
                $collection = new ArrayCollection();
                $stop = false;
                $index = 0;

                while (!$stop) {
                    $localPref = $prefix.$relation->field.'.'.$index.'.';
                    $localMeta = $this->entityManager->getClassMetadata($relation->class);
                    $localModel = $localMeta->newInstance();

                    $exist = $this->hydrateObject($localModel, $fields, $localPref);
                    if ($exist) {
                        $collection->add($localModel);
                        ++$index;
                    } else {
                        $stop = true;
                    }
                }

                $meta->setFieldValue($model, $relation->field, $collection);
            }
            else {
                $localPref = $prefix.$relation->field.'.';
                $localMeta = $this->entityManager->getClassMetadata($relation->class);
                $localModel = $localMeta->newInstance();

                $this->hydrateObject($localModel, $fields, $localPref);
                $meta->setFieldValue($model, $relation->field, $localModel);
            }
        }

        return true;
    }
}
