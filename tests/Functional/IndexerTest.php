<?php

declare(strict_types=1);

namespace Functional;

use App\Entity\Composition;
use App\Entity\CompositionPartial;
use App\Entity\Material;
use App\Entity\MaterialPartial;
use App\Entity\Product;
use App\Entity\ProductNonSync;
use App\Entity\ProductPartial;
use App\Entity\ProductRelationsNonSync;
use App\Entity\Properties;
use App\Entity\PropertiesPartial;
use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
use App\Value\PricePartial;
use Doctrine\ORM\EntityManagerInterface;
use Http\Client\Exception;
use Maratzhe\SymfonyTypesense\Factory\FinderFactory;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\EventListener;
use Maratzhe\SymfonyTypesense\Service\Finder;
use Maratzhe\SymfonyTypesense\Service\Indexer;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Typesense\Exceptions\TypesenseClientError;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class IndexerTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testCreate() : void
    {
        $em = $this->em();

        $this->checkIndex();


        $material1 = new Material( 'denim');
        $material2 = new Material('cotton');

        $product    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition( $material1, 30), new Composition( $material2, 70)],
            new Properties('test_name', 'test_value')
        );

        $product2    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::Black],
            [],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition( $material1, 30), new Composition( $material2, 70)],
            new Properties( 'test_name', 'test_value')
        );


        $em->persist($product);
        $em->persist($product2);


        $this->indexer()->persist($product);
        $this->indexer()->persist($product2);


        $data   = $this->indexer()->toPersist();

        self::assertNotEmpty($data);
        self::assertArrayHasKey(Product::class, $data);
        self::assertCount(2, $data[Product::class]);
        self::assertArrayHasKey($product->id, $data[Product::class]);
        self::assertArrayHasKey($product2->id, $data[Product::class]);
        self::assertCount(18, $data[Product::class][$product->id]);
        self::assertCount(18, $data[Product::class][$product2->id]);



        $this->indexer()->flush();
        self::assertEmpty($this->indexer()->toPersist());


        $searched   = $this->searchById($product->id, Product::class);
        $searched2  = $this->searchById($product2->id, Product::class);

        self::assertNotNull($searched);
        self::assertNotNull($searched2);

        self::assertProductsEquals($product, $searched);
        self::assertProductsEquals($product2, $searched2);
    }

    public function testCreatePartial() : void
    {
        $this->checkIndex();
        $em = $this->em();


        $product = new ProductPartial(
            [Color::White, Color::Red],
            Pattern::Camouflage,
            new PricePartial(30, 'eur'),
            [
                new CompositionPartial( new MaterialPartial('denim'), 30),
                new CompositionPartial(  new MaterialPartial( 'silk'), 70),
            ],
            new PropertiesPartial('test', 'value')
        );

        $em->persist($product);

        $this->indexer()->persist($product);

        $data   = $this->indexer()->toPersist();

        self::assertArrayHasKey(ProductPartial::class, $data);
        self::assertCount(1, $data[ProductPartial::class]);
        self::assertArrayHasKey($product->id, $data[ProductPartial::class]);
        self::assertCount(6, $data[ProductPartial::class][$product->id]);
    }

    public function testRemove() : void
    {
        $this->checkIndex();

        $product    = $this->getTestProduct();
        $productSr  = $this->searchById($product->id, $product::class);

        self::assertNotNull($productSr);

        $this->indexer()->remove($product);

        $data   = $this->indexer()->toRemove();
        self::assertNotEmpty($data);
        self::assertArrayHasKey(Product::class, $data);
        self::assertCount(1, $data[Product::class]);
        self::assertArrayHasKey(0, $data[Product::class]);
        self::assertEquals($product->id, $data[Product::class][0]);

        $this->indexer()->flush();
        self::assertEmpty($this->indexer()->toRemove());

        $productSr  = $this->searchById($product->id, $product::class);

        self::assertNull($productSr);
    }

    public function testEdit() : void
    {
        $em         = $this->em();
        $product    = $this->getTestProduct();

        self::assertCount(2, $product->colors);
        self::assertEquals(Color::Red, $product->colors[1]);

        $product->colors    = [Color::White];

        $em->persist($product);
        $this->indexer()->persist($product);

        $data   = $this->indexer()->toPersist();

        self::assertNotEmpty($data);
        self::assertArrayHasKey(Product::class, $data);
        self::assertCount(1, $data[Product::class]);

        $this->indexer()->flush();

        $data   = $this->indexer()->toPersist();
        self::assertEmpty($data);

        $productSr  = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);

        self::assertProductsEquals($product, $productSr);
    }


    protected function assertProductsEquals(Product $product, Product $productSr) : void
    {
        self::assertEquals($product->id, $productSr->id);

        if($product->custom_id !== null) {
            self::assertNotNull($productSr->custom_id);
            self::assertTrue($product->custom_id->equals($productSr->custom_id));
        }
        else {
            self::assertNull($productSr->custom_id);
        }


        self::assertEquals($product->pattern, $productSr->pattern);

        if($product->price !== null) {
            self::assertNotNull($productSr->price);
            self::assertEquals($product->price->price, $productSr->price->price);
            self::assertEquals($product->price->currency, $productSr->price->currency);
        }
        else {
            self::assertNull($productSr->price);
        }

        self::assertCount(count($product->colors), $productSr->colors);
        for($i=0; $i<count($product->colors); $i++){
            self::assertEquals($product->colors[$i], $productSr->colors[$i]);
        }

        self::assertCount(count($product->photos), $productSr->photos);
        for($i=0; $i<count($product->photos); $i++){
            self::assertEquals($product->photos[$i]->size, $productSr->photos[$i]->size);
            self::assertEquals($product->photos[$i]->url, $productSr->photos[$i]->url);
        }

        self::assertCount(count($product->compositions), $productSr->compositions);
        for($i=0; $i<count($product->compositions); $i++){
            self::assertNotNull($productSr->compositions[$i]);
            self::assertNotNull($product->compositions[$i]);

            self::assertEquals($product->compositions[$i]->id, $productSr->compositions[$i]->id);
            self::assertEquals($product->compositions[$i]->material->id, $productSr->compositions[$i]->material->id);
            self::assertEquals($product->compositions[$i]->material->name, $productSr->compositions[$i]->material->name);
        }

        if($product->properties !== null) {
            self::assertNotNull($productSr->properties);
            self::assertEquals($product->properties->id, $productSr->properties->id);
            self::assertEquals($product->properties->name, $productSr->properties->name);
            self::assertEquals($product->properties->value, $productSr->properties->value);
        }
        else {
            self::assertNull($productSr->properties);
        }
    }


    protected function em() : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em         = self::getContainer()->get(EntityManagerInterface::class);
        $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
        foreach ($events as $event) {
            $searched   = array_find($em->getEventManager()->getListeners($event), fn($ls) => $ls instanceof EventListener);
            if($searched !== null) {
                $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
                $em->getEventManager()->removeEventListener($events, $searched);
            }
        }

        return $em;
    }


    protected function checkIndex() : void
    {
        /** @var CollectionManager $manager */
        $manager    = self::getContainer()->get(CollectionManager::class);

        $classes    = [
            Product::class,
            ProductNonSync::class,
            ProductRelationsNonSync::class,
            ProductRelationsNonSync::class,
        ];

        foreach ($classes as $class) {
            if($manager->exists($class)) {
                $manager->delete($class);
            }

            $manager->create($class);
        }
    }


    protected function getTestProduct(bool $index = true) : Product
    {
        $em         = $this->em();
        $material1  = new Material( 'denim');
        $material2  = new Material('cotton');

        $product    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition( $material1, 30), new Composition( $material2, 70)],
            new Properties('test_name', 'test_value')
        );


        $em->persist($product);
        $em->flush();
        $em->clear();

        if($index) {
            $this->indexer()->persist($product);
            $this->indexer()->flush();
        }

        return $product;
    }

    /**
     * @template T of object
     * @param ?int $id
     * @param class-string<T> $finder
     * @return T|null
     * @throws Exception
     * @throws TypesenseClientError
     */
    protected function searchById(?int $id, string $finder) : object|null
    {
        $result = $this->finder($finder)->query()->filterBy('id :=' . $id)->getResult();

        if(count($result->hits) === 0) {
            return null;
        }

        return $result->hits[0]->document;
    }

    /**
     * @template T of object
     * @param int|null $id
     * @param class-string<T> $class
     * @return T|null
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function find(?int $id, string $class) : object|null
    {
        return $this->em()->find($class, (int)$id);
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

    protected function indexer() : Indexer
    {
        /** @var Indexer $indexer */
        $indexer   = self::getContainer()->get(Indexer::class);

        return $indexer;
    }

    protected function listener() : EventListener
    {
        /** @var EventListener $indexer */
        $indexer   = self::getContainer()->get(EventListener::class);

        return $indexer;
    }

    protected function manager() : CollectionManager
    {
        /** @var CollectionManager $manager */
        $manager   = self::getContainer()->get(CollectionManager::class);

        return $manager;
    }

    protected function transformer() : Transformer
    {
        /** @var Transformer $transformer */
        $transformer = self::getContainer()->get(Transformer::class);

        return $transformer;
    }
}