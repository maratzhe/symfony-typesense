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
use Doctrine\ORM\EntityManagerInterface;
use Maratzhe\SymfonyTypesense\Factory\FinderFactory;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Finder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;



class SearchTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testMultiSearch(): void
    {
        $this->checkIndex();

        $data   = [
            [
                'pattern'   => Pattern::Camouflage,
                'colors'    => [Color::Black, Color::Orange],
                'count'     => 25
            ],
            [
                'pattern'   => Pattern::Camouflage,
                'colors'    => [Color::Black, Color::Blue],
                'count'     => 35
            ],
            [
                'pattern'   => Pattern::Animal,
                'colors'    => [Color::White],
                'count'     => 40
            ],
            [
                'pattern'   => Pattern::Fret,
                'colors'    => [Color::Red],
                'count'     => 10
            ]
        ];

        foreach ($data as $d) {
            $this->createProducts($d['pattern'], $d['colors'], $d['count']);
        }


        $finder     = $this->finder(Product::class);
        $queries    = [
            $finder->query()
                ->page(1)
                ->perPage(10)
                ->filterBy('published := true')
        ];

        $facets = [
            'pattern',
            'colors'
        ];

        foreach ($facets as $fc) {
            $queries[] = $finder->query()
                ->page(1)
                ->perPage(0)
                ->facetBy($fc)
                ->filterBy(implode(' && ', ['published := true']));
        }



        [$main, $patterns, $colors]    = $finder->multiSearch($queries);

        self::assertCount(10,  $main->hits);
        self::assertEquals(11, $main->pages);

        $patternCounts  = [
            Pattern::Camouflage->value  => 60,
            Pattern::Animal->value      => 40,
            Pattern::Fret->value        => 10
        ];


        foreach ($patterns->facet_counts[0]['counts'] as $count) {
            self::assertEquals($count['count'], $patternCounts[$count['value']]);
        }

        $colorCounts   = [
            Color::Black->value     => 60,
            Color::Orange->value    => 25,
            Color::Blue->value      => 35,
            Color::White->value     => 40,
            Color::Red->value       => 10,
        ];

        foreach ($colors->facet_counts[0]['counts'] as $count) {
            self::assertEquals($count['count'], $colorCounts[$count['value']]);
        }

    }


    protected function em() : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em         = self::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }

    protected function checkIndex() : void
    {
        /** @var CollectionManager $manager */
        $manager    = self::getContainer()->get(CollectionManager::class);

        $classes    = [
            Product::class
        ];

        foreach ($classes as $class) {
            if($manager->exists($class)) {
                $manager->delete($class);
            }

            $manager->create($class);
        }
    }


    /**
     * @param Pattern $pattern
     * @param array<int, Color> $colors
     * @param int $count
     */
    protected function createProducts(Pattern $pattern, array $colors, int $count) : void
    {
        for($i = 0; $i < $count; $i++) {
            $product    = new Product(
                new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
                $colors,
                [new Photo(100, 'test_url')],
                $pattern,
                new Price(30, 'eur'),
                [new Composition(new Material( 'denim'), 30), new Composition( new Material('cotton'), 70)],
                new Properties( 'test_name', 'test_value'),
                true
            );

            $this->em()->persist($product);
        }

        $this->em()->flush();
    }

    /**
     * @template T of object
     * @param class-string<T> $finder
     * @return Finder<T>
     */
    protected function finder(string $finder) : Finder
    {
        /** @var FinderFactory $factory */
        $factory    = self::getContainer()->get(FinderFactory::class);

        return $factory->create($finder);
    }
}