<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Command;

use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
use Maratzhe\SymfonyTypesense\Search\ClassMeta;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected Mapper $mapper
    )
    {
        parent::__construct();
    }



    protected function configure(): void
    {
        $this->addOption('all', null, InputOption::VALUE_NONE, 'all indexes');
        $this->addArgument('index', InputArgument::OPTIONAL, 'index name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $collections = $this->getCollections($input, $io);

        if (count($collections) === 0) {
            return Command::FAILURE;
        }

        if ($this->process($collections, $io, $input)) {
            return self::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * @param array<class-string, string> $collections
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return bool
     */
    abstract protected function process(array $collections, SymfonyStyle $io, InputInterface $input): bool;

    /**
     * @return array<class-string, string>
     */
    protected function getCollections(InputInterface $input, SymfonyStyle $io): array
    {
        $all            = $this->optionBool($input,'all');
        $index          = $this->argument($input,'index');
        $classes        = array_filter($this->mapper->classes(), fn(ClassMeta $class): bool => $class->is_collection);
        $indexes    = [];
        foreach ($classes as $class) {
            if($class->collection === null) {
                continue;
            }

            $indexes[$class->class]  = $class->collection->name;
        }

        if (count($indexes) === 0) {
            $io->error('Indexes not found (try use '.SearchCollection::class.' first)');

            return [];
        }

        if (!$all && null === $index) {

            $choice = $io->choice('Select index', array_values($indexes));

            $index  = is_scalar($choice) ? (string)$choice : '';
        }

        if(!$all) {
            $indexes = array_filter($indexes, fn($val) => $val === $index);
            if (count($indexes) === 0) {
                $io->error('Index "' . $index . '" not found');

                return [];
            }

        }

        return $indexes;
    }

    protected function optionString(InputInterface $input, string $name) : string|null
    {
        if($input->getOption($name) === null){
            return null;
        }

        if(!is_scalar($input->getOption($name))) {
            return null;
        }

        return (string)$input->getOption($name);
    }

    protected function optionBool(InputInterface $input, string $name) : bool
    {
        if($input->getOption($name) === null){
            return false;
        }

        if(!is_scalar($input->getOption($name))) {
            return false;
        }

        return (bool)$input->getOption($name);
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
