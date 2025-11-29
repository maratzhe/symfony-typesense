<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Exception;
use Maratzhe\SymfonyTypesense\Enum\SyncMode;


class EventListener
{
    /**
     * @var array<class-string, array<string>>
     *
     */
    protected array $deleted  = [];

    /**
     * @var array<class-string, array<string>>
     */
    protected array $persisted = [];

    public function __construct(
        protected CollectionManager $collectionManager,
        protected Transformer $transformer,
        protected Mapper $mapper,
        protected Indexer $indexer,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->persist($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->persist($args->getObject());
    }

    protected function persist(object $object): void
    {
        $roots = $this->roots($object);

        foreach ($roots as $root) {
            $meta   = $this->mapper->meta($root);
            if($meta === null) {
                continue;
            }
            $class  = $meta->class;
            if(!isset($this->persisted[$class])) {
                $this->persisted[$class] = [];
            }

            $id     = $this->transformer->id($root);
            if($id === '') {
                continue;
            }

            $this->persisted[$class][] = $this->transformer->id($root);
            $this->indexer->persist($root);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $meta   = $this->mapper->meta($entity);
        if($meta === null) {
            return;
        }


        if($meta->is_collection) {
            if(!isset($this->deleted[$meta->class])) {
                $this->deleted[$meta->class] = [];
            }

            $this->deleted[$meta->class][]    = $this->transformer->id($entity);
            $this->indexer->remove($entity);
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $meta   = $this->mapper->meta($entity);
        if($meta === null) {
            return;
        }

        if(!$meta->is_collection) {
            $this->persist($entity);
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $this->indexer->flush();

        $this->persisted    = [];
        $this->deleted      = [];
    }

    /**
     * @param object $entity
     * @return object[]
     * @throws Exception
     */
    protected function roots(object $entity): array
    {
        $meta   = $this->mapper->meta($entity);

        if($meta === null) {
            return [];
        }

        if($meta->is_collection) {
            if($meta->collection !== null && $meta->collection->sync === SyncMode::AUTO) {
                return [$entity];
            }
            else {
                return [];
            }
        }

        if(count($meta->relations) === 0) {
            return [];
        }

        $roots  = [];
        foreach ($meta->relations as $relation) {
            if($relation->child) {
                continue;
            }

            $relationMeta   = $this->mapper->meta($relation->class);
            if($relationMeta === null) {
                continue;
            }

            $mapping        =  $relationMeta->relations[(string)$relation->reverse]->relation;
            if($mapping === null || $mapping->sync !== SyncMode::AUTO) {
                continue;
            }

            /** @var object $value */
            $value  = $this->mapper->metaORM($entity)->getFieldValue($entity, $relation->field);
            if ($value instanceof Collection) {
                foreach ($value as $item) {
                    /** @var object $item */
                    $roots = [...$roots, ...$this->roots($item)];
                }
            } else {
                $roots = [...$roots, ...$this->roots($value)];
            }
        }

        return $roots;
    }
}
