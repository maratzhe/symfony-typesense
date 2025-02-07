<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Command;

use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Search\ClassMeta;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Symfony\Component\String\u;

#[AsCommand(
    name: 'search:show',
    description: 'Show document from collection',
)]
class ShowCommand extends Command
{
    public function __construct(
        protected CollectionManager $collectionManager,
        protected Mapper $mapper,
    ) {
        parent::__construct('search:show');
    }

    protected function configure()
    {
        $this->addArgument('index', InputArgument::REQUIRED, 'index name');
        $this->addArgument('id', InputArgument::REQUIRED, 'id of entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $index      = $this->argument($input, 'index');
        $id         = $this->argument($input, 'id');
        $classes    = array_filter($this->mapper->classes(), fn(ClassMeta $class): bool => $class->is_collection);
        $indexes    = [];
        foreach ($classes as $class) {
            if($class->collection === null) {
                continue;
            }

            $indexes[$class->collection->name]  = $class->class;
        }

        if (count($indexes) === 0) {
            $io->error('Indexes not found (try use '.SearchCollection::class.' first)');

            return self::FAILURE;
        }

        if (!isset($indexes[$index])) {
            $io->error('Index "'.$index.'" not found');

            return self::FAILURE;
        }


        $collection = $this->collectionManager->get($indexes[$index]);

        $query = [
            'q' => '*',
            'filter_by' => 'id := '.$id,
        ];

        /** @var \TypesenseResult $result */
        $result = $collection->documents->search($query);

        if (count($result['hits']) === 0) {
            $io->error('Entity "'.$id.'" not found in collection "'.$index.'"');

            return self::FAILURE;
        }

        $document = $result['hits'][0]['document'];
        $rows = [];

        foreach ($document as $key => $value) {
            $rows[] = [$key, u((string) $value)->wordwrap(120, cut: true)];
            $rows[] = new TableSeparator();
        }

        array_pop($rows);

        $io->newLine();
        $io->writeln(sprintf(' <info>Data for</info> <comment>%s %s</comment> (class: <comment>%s</comment>)', $index, $id, $indexes[$index]));
        $io->createTable()
            ->setColumnWidths([20, 120])
            ->setHeaders(['field', 'value'])
            ->setRows($rows)
            ->setStyle('box-double')
            ->render();

        return self::SUCCESS;
    }

    protected function argument(InputInterface $input, string $name) : string|null
    {

        if($input->getArgument($name) === null){
            return null;
        }

        if(!is_scalar($input->getArgument($name))) {
            return null;
        }

        return (string)$input->getArgument($name);
    }
}
