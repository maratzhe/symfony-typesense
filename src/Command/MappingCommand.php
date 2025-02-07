<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Command;

use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;



#[AsCommand(
    name: 'search:mapping',
    description: 'Show collection mapping',
)]
class MappingCommand extends AbstractCommand
{
    public function __construct(
        protected CollectionManager $collectionManager,
        Mapper $mapper,
    ) {
        parent::__construct($mapper);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('real', null, InputOption::VALUE_NONE, 'show mapping from typesense');
    }

    /**
     * @param array<class-string, string> $collections
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return bool
     */
    protected function process(array $collections, SymfonyStyle $io, InputInterface $input): bool
    {
        $real = $this->optionBool($input, 'real');
        $type = $real ? 'typesense' : 'generated';

        foreach ($collections as $class => $collection) {
            $fields = $this->getMapping($class, $real);
            $rows   = [];
            foreach ($fields as $field) {
                $rows[] = [
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'locale' => $field['locale'],
                    'optional' => $field['optional'] === true ? '<info>yes</info>' : '<comment>no</comment>',
                    'facet' => $field['facet']  === true ? '<info>yes</info>' : '<comment>no</comment>',
                    'index' => $field['index']  === true ? '<info>yes</info>' : '<comment>no</comment>',
                    'infix' => $field['infix']  === true ? '<info>yes</info>' : '<comment>no</comment>',
                    'sort' => $field['sort']  === true ? '<info>yes</info>' : '<comment>no</comment>',
                    'stem' => $field['stem']  === true ? '<info>yes</info>' : '<comment>no</comment>',
                ];

                $rows[] = new TableSeparator();
            }

            array_pop($rows);

            $io->writeln('');
            $io->writeln(sprintf(' <info>Mapping of</info> <comment>%s</comment> (class: <comment>%s</comment>), %s', $collection, $class, $type));

            $titles = count($fields) !== 0 ? array_keys($fields[0]) : [];


            $io->createTable()
                ->setHeaders($titles)
                ->setRows($rows)
                ->setStyle('box-double')
                ->render();
        }

        return true;
    }

    /**
     * @param string $class
     * @param bool $real
     * @return \TypesenseField[] | array<int, array<string, string|bool|float|int>>
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    protected function getMapping(string $class, bool $real): array
    {
        if ($real) {
            /** @var \TypesenseField[] $fields */
            $fields   = $this->collectionManager->get($class)->retrieve()['fields'] ?? [];

            return $fields;
        } else {
            return $this->mapper->mapping($class)['fields'];
        }
    }
}
