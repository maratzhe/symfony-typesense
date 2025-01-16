<?php

declare(strict_types=1);

namespace Functional;

use App\Entity\Product;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class CollectionManagerTest extends KernelTestCase
{
    public function testManager() : void
    {
        /** @var CollectionManager $manager */
        $manager    = $this->getContainer()->get(CollectionManager::class);

        if($manager->exists(Product::class)) {
            $manager->delete(Product::class);
        }

        $collection = $manager->create(Product::class);

        $this->assertArrayHasKey('created_at', $collection);
    }
}