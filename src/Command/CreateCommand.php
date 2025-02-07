<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Command;

use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Typesense\Exceptions\ObjectNotFound;

#[AsCommand(
    name: 'search:create',
    description: 'create search index',
)]
class CreateCommand extends AbstractCommand
{
    public function __construct(
        protected CollectionManager $collectionManager,
        Mapper $mapper,
    ) {
        parent::__construct($mapper);
    }

    /**
     * @param array<class-string, string> $collections
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return bool
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    protected function process(array $collections, SymfonyStyle $io, InputInterface $input): bool
    {
        foreach ($collections as $class => $collection) {
            try {
                $io->writeln(sprintf('<info>Deleting</info> <comment>%s</comment> (class: <comment>%s</comment>)', $collection, $class));
                $this->collectionManager->delete($class);
            } catch (ObjectNotFound $exception) {
                $io->writeln(sprintf('Collection <comment>%s</comment> <info>does not exists</info> ', $collection));
            } catch (\Throwable $exception) {
                $io->error('Network error: '.$exception->getMessage());

                return false;
            }

            $io->writeln(sprintf('<info>Creating</info> <comment>%s</comment> (class: <comment>%s</comment>)', $collection, $class));

            $this->collectionManager->create($class);
        }

        return true;
    }
}
