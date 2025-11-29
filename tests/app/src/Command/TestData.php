<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Composition;
use App\Entity\Material;
use App\Entity\Product;
use App\Entity\Properties;
use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'test:data',
    description: 'generate test data',
)]
class TestData extends Command
{
    /**
     * @param EntityManagerInterface $entityManager
     * @throws LogicException When the command name is empty
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        for($i = 1; $i <= 10000; $i++) {
            $product    = new Product(
                new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
                [Color::White, Color::Red],
                [new Photo(100, 'test_url')],
                Pattern::Animal,
                new Price(30, 'eur'),
                [new Composition( new Material('test 1'), 30), new Composition( new Material('test 2'), 70)],
                new Properties('test_name', 'test_value')
            );

            $this->entityManager->persist($product);


            if($i % 100 === 0) {
                $output->writeln(sprintf('<info>items: %s</info>', $i));
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return self::SUCCESS;
    }
}