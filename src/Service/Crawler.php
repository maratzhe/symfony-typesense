<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Crawler
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Mapper $mapper
    )
    {

    }

    public function total(string $class) : int
    {
        return (int)$this->entityManager->createQuery('SELECT COUNT(e) FROM '.$class.' e')->getSingleScalarResult();
    }


    /**
     * @template T
     * @param class-string<T> $class
     * @param int $page
     * @param int $perPage
     * @return Paginator<T>
     */
    public function paginator(string $class, int $page, int $perPage) : Paginator
    {
        $meta   = $this->mapper->meta($class);
        assert(is_object($meta));
        $joins  = $this->getJoins($class, 'entity');
        $select = ['entity', ...array_keys($joins)];

        $query  = $this->entityManager->createQueryBuilder()
            ->select($select)
            ->from($meta->class, 'entity')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        foreach ($joins as $alias => $field) {
            $query->leftJoin($field, $alias);
        }

        /** @var Paginator<T> */
        return new Paginator($query);
    }

    public function clear() : void
    {
        $this->entityManager->clear();
    }

    /**
     * @param string $class
     * @param string $parent
     * @return array<string, string>
     */
    public function getJoins(string $class, string $parent): array
    {
        $data   = [];
        $meta    = $this->mapper->meta($class);
        if($meta === null) {
            return $data;
        }


        foreach ($meta->relations as $relation) {
            if(!$relation->child || $relation->relation === null || !$relation->relation->bulk) {
                continue;
            }

            $data[$relation->field] = $parent . '.' . $relation->field;

            $data   = [...$data, ...$this->getJoins($relation->class, $relation->field)];
        }

        return $data;
    }
}
