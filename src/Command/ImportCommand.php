<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Command;

use Doctrine\ORM\EntityManagerInterface;
use Maratzhe\SymfonyTypesense\Service\Crawler;
use Maratzhe\SymfonyTypesense\Service\Indexer;
use Maratzhe\SymfonyTypesense\Service\Mapper;
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
        protected Indexer $indexer,
        protected Mapper $mapper,
        protected Crawler $crawler
    ) {
        parent::__construct($mapper);
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
    protected function populateIndex(SymfonyStyle $io, string $collection, string $class, int $firstPage, int $perPage, string|null $lastPage = null): int
    {
        $populated = 0;
        $total = $this->crawler->total($class);
        $pages = ceil($total / $perPage);
        $last = (int) ($lastPage ?? $pages);

        if ($last < $firstPage) {
            throw new \Exception('The first-page option ('.$firstPage.') is bigger than the last-page option ('.$lastPage.')');
        }

        $io->text(sprintf('<info>[%s] %s</info> %s entries to insert splited into %s pages of %s elements. Insertion from page %s to %s.',
            $collection, $class, $total, $pages, $perPage, $firstPage, $last
        ));

        for ($i = $firstPage; $i <= $last; ++$i) {
            $entities   = $this->crawler->paginator($class, $i, $perPage);
            foreach ($entities as $entity) {
                $this->indexer->persist($entity);
            }

            $toPersist  = count($this->indexer->toPersist()[$class]);

            $io->text(sprintf('Import <info>[%s] %s</info> Page %s of %s (%s items)',
                $collection, $class, $i, $last, $toPersist
            ));


            $populated += $toPersist;

            $this->indexer->flush();
            $this->crawler->clear();
        }

        $io->newLine();

        return $populated;
    }
}
