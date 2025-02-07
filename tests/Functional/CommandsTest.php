<?php
declare(strict_types=1);

namespace Functional;




use App\Entity\Composition;
use App\Entity\Material;
use App\Entity\Product;
use App\Entity\Properties;
use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
use Doctrine\Bundle\DoctrineBundle\Middleware\BacktraceDebugDataHolder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\ResetDatabase;



class CommandsTest extends KernelTestCase
{
    use ResetDatabase;

    public function testCreateCommand() : void
    {
        $tester = $this->createCommandTester('search:create');
        $tester->execute(['index' => 'product']);

        $output = $tester->getDisplay();

        self::assertStringContainsString('Deleting product (class: ' . Product::class . ')', $output);
        self::assertStringContainsString('Creating product (class: ' . Product::class . ')', $output);
    }

    public function testMappingCommand() : void
    {
        $tester = $this->createCommandTester('search:mapping');
        $tester->execute(['index' => 'product']);

        $output = $tester->getDisplay();

        self::assertStringContainsString('Mapping of product (class: ' . Product::class . ')', $output);
    }

    public function testShowCommand() : void
    {
        $product    = $this->createTestProducts(1)[0];
        $tester = $this->createCommandTester( 'search:show');
        $tester->execute(['index' => 'product', 'id' => $product->id]);

        $output = $tester->getDisplay();

        self::assertStringContainsString('Data for product ' . $product->id . ' (class: ' . Product::class . ')', $output);
    }


    #[TestWith([10, 1])]
    #[TestWith([42, 10])]
    #[TestWith([130])]
    #[TestWith([493, 50, 8, 10, 143])]
    public function testImportCommand(int $total, ?int $perPage = null, ?int $first = null, ?int $lastPage = null, ?int $expected = null) : void
    {
        $this->createTestProducts($total, false);
        $tester     = $this->createCommandTester( 'search:import');

        $options    = [
            'index' => 'product'
        ];

        if ($perPage !== null) {
            $options['--per-page'] = $perPage;
        }
        if ($first !== null) {
            $options['--first-page'] = $first;
        }
        if ($lastPage !== null) {
            $options['--last-page'] = $lastPage;
        }

        $tester->execute($options);
        $output = $tester->getDisplay();


        self::assertStringContainsString(
            sprintf('[product] %s %s entries to insert', Product::class, $total),
            $output
        );

        self::assertStringContainsString(
            sprintf('[OK] %s elements populated', $expected ?? $total),
            $output
        );
    }

    /**
     * @param int $count
     * @param bool $save
     * @return array<int, Product>
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function createTestProducts(int $count, bool $save = true) : array
    {

        $products = [];
        for($i = 1; $i <= $count; $i++) {
            $product    = new Product(
                new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
                [Color::White, Color::Red],
                [new Photo(100, 'test_url')],
                Pattern::Animal,
                new Price(30, 'eur'),
                [new Composition( new Material('test 1'), 30), new Composition( new Material('test 2'), 70)],
                new Properties('test_name', 'test_value')
            );

            $this->em()->persist($product);

            if($save) {
                $products[] = $product;
            }


            if($i % 100 === 0) {
                $this->em()->flush();
                $this->em()->clear();


                if(self::getContainer()->has('doctrine.debug_data_holder')) {
                    /** @var BacktraceDebugDataHolder $debug */
                    $debug  = self::getContainer()->get('doctrine.debug_data_holder');
                    $debug->reset();
                }
            }
        }

        $this->em()->flush();
        $this->em()->clear();

        return $products;
    }

    protected function em() : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }

    protected function createCommandTester(string $command) : CommandTester
    {
        self::bootKernel();

        if(self::$kernel === null) {
            throw new \RuntimeException('Kernel not booting');
        }

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        return new CommandTester($application->find($command));
    }

}