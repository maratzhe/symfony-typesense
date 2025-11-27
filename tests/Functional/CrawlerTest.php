<?php

declare(strict_types=1);

namespace Functional;

use App\Entity\Product;
use App\Entity\ProductManyToOneRelation;
use Maratzhe\SymfonyTypesense\Service\Crawler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class CrawlerTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;


    public function testJoins() : void
    {
        $crawler    = $this->getCrawler();
        $joins      = $crawler->getJoins(Product::class, 'product');

        self::assertCount(2, $joins);
    }


    public function testManyToOneRelationJoins() : void
    {
        $crawler    = $this->getCrawler();
        $joins      = $crawler->getJoins(ProductManyToOneRelation::class, 'product');

        self::assertCount(1, $joins);
    }

    protected function getCrawler() : Crawler
    {
        /** @var Crawler */
        return self::getContainer()->get(Crawler::class);
    }
}