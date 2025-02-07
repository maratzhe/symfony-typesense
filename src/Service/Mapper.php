<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\InverseSideMapping;
use Doctrine\ORM\Mapping\ManyToOneAssociationMapping;
use Doctrine\ORM\Mapping\MappingException;
use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Attribute\SearchField;
use Maratzhe\SymfonyTypesense\Attribute\SearchRelation;
use Maratzhe\SymfonyTypesense\Enum\FieldType;
use Maratzhe\SymfonyTypesense\Search\ClassMeta;
use Maratzhe\SymfonyTypesense\Search\CollectionMapping;
use Maratzhe\SymfonyTypesense\Search\FieldMapping;
use Maratzhe\SymfonyTypesense\Search\PropertyMeta;
use Maratzhe\SymfonyTypesense\Search\RelationMapping;
use Maratzhe\SymfonyTypesense\Search\RelationMeta;


class Mapper
{
    /** @var array<string, array{name:string, enable_nested_fields: bool, fields: array<int, array<string, string|bool|float|int>>}> */
    protected array $collections;

    /**
     * @var array<string, ClassMeta>
     */
    protected array $classes;

    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
        $this->classes      = self::mapClasses($this->entityManager);
        $this->collections  = self::mapCollections($this->classes);
    }

    /**
     * @return array<string, ClassMeta>
     */
    public function classes() : array
    {
        return $this->classes;
    }

    public function meta(string|object $entity) : ?ClassMeta
    {
        if(is_string($entity)) {
            return $this->classes[$entity] ?? null;
        }

        return $this->classes[$entity::class] ?? null;
    }

    /**
     * @template T of object
     * @param class-string<T> | T $entity
     * @return ClassMetadata<T>
     */
    public function metaORM(string|object $entity) : ClassMetadata
    {
        if(is_object($entity)) {
            $entity = $entity::class;
        }

        return $this->entityManager->getClassMetadata($entity);
    }

    /**
     * @param string $class
     * @return array{name:string, enable_nested_fields: bool, fields: array<int, array<string, string|bool|float|int>>}
     */
    public function mapping(string $class) : array
    {
        return $this->collections[$class] ?? ['name' => '', 'enable_nested_fields' => false, 'fields' => []];
    }

    /**
     * @param class-string $class
     * @param EntityManagerInterface $entityManager
     * @return ClassMeta|null
     * @throws MappingException
     */
    public static function mapClass(string $class, EntityManagerInterface $entityManager) : ?ClassMeta
    {
        $meta = $entityManager->getClassMetadata($class);
        if($meta->reflClass === null) {
            return null;
        }

        $collection = $meta->reflClass->getAttributes(SearchCollection::class)[0] ?? null;
        $embeddable = $meta->reflClass->getAttributes(Embeddable::class)[0] ?? null;
        $fields     = [];
        $relations  = [];


        foreach ($meta->reflClass->getProperties() as $property) {
            $attributes     = array_map(function($el) use($property) {
                /** @var SearchField $attribute */
                $attribute  = $el->newInstance();
                $name       = $attribute->name ?? $property->name;

                return new FieldMapping(
                    $name,
                    $attribute->type,
                    $attribute->locale,
                    $attribute->optional,
                    $attribute->facet,
                    $attribute->index,
                    $attribute->infix,
                    $attribute->sort,
                    $attribute->stem
                );
            }, $property->getAttributes(SearchField::class));



            $identifier     = in_array($property->name, $meta->getIdentifierFieldNames(), true);
            $embedded       = $property->getAttributes(Embedded::class)[0] ?? null;
            $mapping        = $meta->fieldMappings[$property->name] ?? null;

            /** @var class-string $embeddedClass */
            $embeddedClass  = $embedded?->newInstance()->class ?? null;


            if(count($attributes) !== 0 || $identifier || $embedded !== null) {
                if(count($attributes) === 0 && $identifier) {
                    $attributes[]   = new FieldMapping($property->name, null, '', true, false, false, false, false, false);
                }

                $fields[$property->name] = new PropertyMeta(
                    $meta->name,
                    $property->name,
                    $mapping?->type,
                    $embedded !== null,
                    $embeddedClass,
                    $attributes
                );
            }
        }


        foreach ($meta->associationMappings as $association) {
            $property           = $meta->getReflectionProperty($association->fieldName);
            $relationMeta       = $entityManager->getClassMetadata($association->targetEntity);
            $reverseProperty    = null;
            $one                = true;

            if($property === null) {
                continue;
            }

            if($association instanceof InverseSideMapping) {
                $one                = false;
                $reverseProperty = $relationMeta->getReflectionProperties()[$association->mappedBy] ?? null;
            }

            if($association instanceof ManyToOneAssociationMapping && $association->inversedBy !== null) {
                $reverseProperty = $relationMeta->getReflectionProperties()[$association->inversedBy] ?? null;
            }


            $search = $property->getAttributes(SearchRelation::class)[0] ?? null;
            $child = $search !== null;
            if(!$child) {
                if($reverseProperty === null) {
                    continue;
                }

                if(count($reverseProperty->getAttributes(SearchRelation::class)) === 0) {
                   continue;
                }
            }

            $mapping    = null;
            if($search !== null) {
                $attribute  = $search->newInstance();
                $mapping    = new RelationMapping($attribute->sync, $attribute->bulk);
            }

            $relations[$property->name] = new RelationMeta(
                $relationMeta->name,
                $property->name,
                $reverseProperty->name ?? null,
                $child,
                $one,
                $mapping
            );
        }

        if($collection === null && count($fields) === 0 && count($relations) === 0) {
            return null;
        }

        $mapping    = null;
        if($collection !== null) {
            $attribute  = $collection->newInstance();
            $name       = $attribute->name ?? $meta->table['name'];
            $mapping    = new CollectionMapping($name, $attribute->sync);
        }

        return new ClassMeta(
            $class,
            $collection !== null,
            $embeddable !== null,
            $fields,
            $relations,
            $mapping
        );
    }

    /**
     * @param array<string, ClassMeta> $classes
     * @return array<string, array{name:string, enable_nested_fields: bool, fields: array<int, array<string, string|bool|float|int>>}>
     */
    public static function mapCollections(array $classes) : array
    {
        $collections = [];
        foreach ($classes as $class) {
            if($class->is_collection) {
                $collection = self::mapCollection($class->class, $classes);
                if($collection !== null) {
                    $collections[$class->class] = $collection;
                }
            }
        }

        return $collections;
    }

    /**
     * @param string $class
     * @param array<string, ClassMeta> $classes
     * @return array{name:string, enable_nested_fields: bool, fields: array<int, array<string, string|bool|float|int>>}|null
     */
    public static function mapCollection(string $class, array $classes) : ?array
    {
        $class = $classes[$class] ?? null;
        if($class === null || !$class->is_collection || $class->collection === null) {
            return null;
        }

        return [
            'name'                  => $class->collection->name,
            'enable_nested_fields'  => true,
            'fields'                => self::mapCollectionFields($class->class, $classes, ''),
        ];
    }

    /**
     * @param string $class
     * @param array<string, ClassMeta> $classes
     * @param string $prefix
     * @return array<int, array<string, string|bool|float|int>>
     */
    protected static function mapCollectionFields(string $class, array $classes, string $prefix) : array
    {
        $class  = $classes[$class] ?? null;
        $fields = [];
        if($class === null) {
            return $fields;
        }

        foreach ($class->fields as $field) {
            if($field->is_embedded && $field->embeddedClass !== null) {
                $fields = [...$fields, ...self::mapCollectionFields($field->embeddedClass, $classes, $prefix . $field->name . '.')];
            }
            else {
                foreach ($field->mapping as $mapping) {
                    if($field->type === null) {
                        continue;
                    }

                    $name   = $prefix . ($mapping->name ?? $field->name);

                    $type   = $mapping->type ?? FieldType::fromDoctrineType($field->type);

                    $fields[]  = [
                        'name'      => $name,
                        'type'      => $type->value,
                        'locale'    => $mapping->locale,
                        'optional'  => $mapping->optional,
                        'facet'     => $mapping->facet,
                        'index'      => $mapping->index,
                        'infix'      => $mapping->infix,
                        'sort'       => $mapping->sort,
                        'stem'       => $mapping->stem,
                    ];
                }
            }
        }



        foreach ($class->relations as $relation) {
            if(!$relation->child) {
                continue;
            }

            $objectPref = $relation->to_one ? $prefix.$relation->field : $prefix.$relation->field.'.*';
            $localPref  = $relation->to_one ? $prefix . $relation->field.'.' : $prefix . $relation->field.'.*.';
            $type       = $relation->to_one ? 'object' : 'object[]';

            $fields     = [
                ...$fields,
                ...[[
                    'name'      => $objectPref,
                    'type'      => $type,
                    'locale'    => '',
                    'optional'  => true,
                    'facet'     => false,
                    'index'     => false,
                    'infix'     => false,
                    'sort'      => false,
                    'stem'      => false,
                ]],
                ...self::mapCollectionFields($relation->class, $classes, $localPref)
            ];
        }

        return $fields;
    }



    /**
     * @param EntityManagerInterface $entityManager
     * @return array<string, ClassMeta>
     * @throws MappingException
     */
    public static function mapClasses(EntityManagerInterface $entityManager): array
    {
        $all            = $entityManager->getMetadataFactory()->getAllMetadata();
        $classes        = [];
        foreach ($all as $data) {
            $meta   = self::mapClass($data->name, $entityManager);
            if($meta !== null) {
                $classes[$meta->class] = $meta;
            }

            foreach ($data->embeddedClasses as $embeddedClass) {
                $meta   = self::mapClass($embeddedClass->class, $entityManager);
                if($meta !== null) {
                    $classes[$meta->class] = $meta;
                }
            }
        }

        return $classes;
    }

}
