<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Indexer;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'search:import',
    description: 'Import collections from Database',
)]
class ImportCommand extends AbstractCommand
{
    protected bool $isError = false;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Mapper $mapper,
        protected Indexer $indexer,
    ) {
        parent::__construct('search:import');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('first-page', null, InputOption::VALUE_REQUIRED, 'page to start population from', 1);
        $this->addOption('last-page', null, InputOption::VALUE_REQUIRED, 'page to end population on');
        $this->addOption('per-page', null, InputOption::VALUE_REQUIRED, 'entities per page', 1000);
    }

    /**
     * @param array<class-string, string> $collections
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return bool
     */
    protected function process(array $collections, SymfonyStyle $io, InputInterface $input): bool
    {
        if(!is_scalar($input->getOption('first-page'))) {
            return false;
        }

        $firstPage  = (int)$this->optionString($input, 'first-page');
        $perPage    = (int) $this->optionString($input,'per-page');
        $lastPage   = $this->optionString($input,'last-page');
        $execStart  = microtime(true);
        $populated  = 0;

        $io->newLine();

        foreach ($collections as $class => $collection) {
            try {
                $populated += $this->populateIndex($io, $collection, $class, $firstPage, $perPage, $lastPage);
            } catch (\Throwable $e) {
                $this->isError = true;
                $io->error($e->getMessage());

                return false;
            }
        }

        $io->newLine();
        if (!$this->isError) {
            $io->success(sprintf(
                '%s element%s populated in %s seconds',
                $populated,
                $populated > 1 ? 's' : '',
                round(microtime(true) - $execStart, PHP_ROUND_HALF_DOWN)
            ));
        }

        return true;
    }


    /**
     * @param SymfonyStyle $io
     * @param string $collection
     * @param class-string $class
     * @param int $firstPage
     * @param int $perPage
     * @param string|null $lastPage
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Http\Client\Exception
     * @throws \JsonException
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    private function populateIndex(SymfonyStyle $io, string $collection, string $class, int $firstPage, int $perPage, string|null $lastPage = null): int
    {
        $populated = 0;
        $total = (int) $this->entityManager->createQuery('SELECT COUNT(e) FROM '.$class.' e')->getSingleScalarResult();
        $pages = ceil($total / $perPage);
        $last = (int) ($lastPage ?? $pages);

        if ($last < $firstPage) {
            throw new \Exception('The first-page option ('.$firstPage.') is bigger than the last-page option ('.$lastPage.')');
        }

        $io->text(sprintf('<info>[%s] %s</info> %s entries to insert splited into %s pages of %s elements. Insertion from page %s to %s.',
            $collection, $class, $total, $pages, $perPage, $firstPage, $last
        ));

        for ($i = $firstPage; $i <= $last; ++$i) {
            $meta   = $this->mapper->meta($class);
            assert(is_object($meta));
            $joins  = $this->getJoins($class, 'entity');
            $select = ['entity', ...array_keys($joins)];

            $query  = $this->entityManager->createQueryBuilder()
                ->select($select)
                ->from($meta->class, 'entity')
                ->setFirstResult(($i - 1) * $perPage)
                ->setMaxResults($perPage);

            foreach ($joins as $alias => $field) {
                $query->leftJoin($field, $alias);
            }

            $entities = new Paginator($query);
            foreach ($entities as $entity) {
                assert(is_object($entity));
                $this->indexer->persist($entity);
            }

            $toPersist  = count($this->indexer->toPersist()[$class]);

            $io->text(sprintf('Import <info>[%s] %s</info> Page %s of %s (%s items)',
                $collection, $class, $i, $last, $toPersist
            ));


            $populated += $toPersist;

            $this->indexer->flush();

            $this->entityManager->clear();
        }

        $io->newLine();

        return $populated;
    }

    /**
     * @param string $class
     * @param string $parent
     * @return array<string, string>
     */
    protected function getJoins(string $class, string $parent): array
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
