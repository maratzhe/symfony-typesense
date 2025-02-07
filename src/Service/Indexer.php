<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;


use Exception;

class Indexer
{
    /**
     * @var array<class-string, array<string, array<string, string|null|int|float|bool>>> $persist
     */
    protected array $persist = [];

    /**
     * @var array<class-string, array<string, array<string, string|null|int|float|bool>>> $update
     */
    protected array $update = [];

    /**
     * @var array<class-string, array<int, string>> $remove
     */
    protected array $remove = [];

    public function __construct(
        protected CollectionManager $collectionManager,
        protected Transformer $transformer,
        protected Mapper $mapper,
    ) {
    }


    /**
     * @template T of object
     * @param T $entity
     * @return void
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function persist(object $entity) : void
    {
        $meta   = $this->mapper->meta($entity);
        if($meta === null) {
            throw new Exception('Meta for entity "' . $entity::class . '" not found.');
        }
        $class  = $meta->class;

        if(!isset($this->persist[$class])) {
            $this->persist[$class]    = [];
        }

        $id     = $this->transformer->id($entity);
        $data   = $this->transformer->normalize($entity);

        $this->persist[$class][$id] = $data;
    }

    /**
     * @template T of object
     * @param T $entity
     * @return void
     */
    public function remove(object $entity): void
    {
        $meta   = $this->mapper->meta($entity);
        if($meta === null) {
            throw new Exception('Meta for entity "' . $entity::class . '" not found.');
        }

        $class  = $meta->class;

        if(!isset($this->remove[$class])) {
            $this->remove[$class]    = [];
        }

        $id     = $this->transformer->id($entity);
        if(!in_array($id, $this->remove[$class], true)) {
            $this->remove[$class][]   = $id;
        }
    }

    public function flush(): void
    {
        foreach ($this->persist as $class => $entities) {
            $this->collectionManager->get($class)->documents->import($entities, ['action' => 'upsert']);
        }

        foreach ($this->remove as $class => $ids) {
            $this->collectionManager->get($class)->documents->delete(['filter_by' => 'id:[' . implode(',', $ids) . ']']);
        }

        $this->persist  = [];
        $this->remove   = [];
    }

    /**
     * @return array<class-string, array<string, array<string, string|null|int|float|bool>>>
     */
    public function toPersist(): array
    {
        return $this->persist;
    }

    /**
     * @return array<class-string, array<int, string>>
     */
    public function toRemove(): array
    {
        return $this->remove;
    }
}
