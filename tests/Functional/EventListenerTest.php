<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Entity\Composition;
use App\Entity\CompositionNonSync;
use App\Entity\Material;
use App\Entity\MaterialNonSync;
use App\Entity\Product;
use App\Entity\ProductNonSync;
use App\Entity\ProductRelationsNonSync;
use App\Entity\Properties;
use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Http\Client\Exception;
use Maratzhe\SymfonyTypesense\Factory\FinderFactory;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Finder;
use Maratzhe\SymfonyTypesense\Service\EventListener;
use Maratzhe\SymfonyTypesense\Service\Indexer;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Typesense\Exceptions\TypesenseClientError;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class EventListenerTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testCreate() : void
    {
        $this->checkIndex();
        $em = $this->em(['postPersist', 'postFlush']);

        $material1 = new Material( 'denim');
        $material2 = new Material('cotton');

        $product    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition($material1, 30), new Composition( $material2, 70)],
            new Properties('test_name', 'test_value')
        );

        $em->persist($product);
        $em->flush();

        $productSr  = $this->searchById( $product->id, $product::class);
        self::assertNotEmpty($productSr);
        self::assertProductsEquals($product, $productSr);
    }


    public function testRemove() : void
    {
        $em         = $this->em(['preRemove', 'postRemove', 'postFlush']);
        $product    = $this->getTestProduct();
        $id         = $product->id;

        $this->indexer()->persist($product);
        $this->indexer()->flush();


        $productSr  = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);

        $em->remove($product);
        $em->flush();

        $productDb  = $this->find($id, $product::class);
        self::assertNull($productDb);

        $productSr  = $this->searchById($id, $product::class);
        self::assertNull($productSr);
    }

    public function testEdit() : void
    {
        $product    = $this->getTestProduct();
        $em         = $this->em(['postUpdate', 'postFlush']);
        self::assertEquals(Pattern::Animal, $product->pattern);

        $product->pattern = Pattern::Camouflage;
        $em->persist($product);
        $em->flush();

        $productSr = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);
        self::assertProductsEquals($product, $productSr);
    }

    public function testEditRelation() : void
    {
        $product    = $this->getTestProduct([]);
        $em         = $this->em(['postUpdate', 'postFlush']);

        self::assertCount(2, $product->compositions);
        self::assertNotNull($product->compositions[1]);
        $material   = $product->compositions[1]->material;
        self::assertEquals('cotton', $material->name);


        $material->name = 'silk';
        $em->persist($material);
        $em->flush();


        $product  = $this->find($product->id, $product::class);
        self::assertNotNull($product);
        $productSr  = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);

        self::assertNotNull($product->compositions[1]);
        $material = $product->compositions[1]->material;
        self::assertEquals('silk', $material->name);

        self::assertProductsEquals($product, $productSr);
    }

    public function testAddRelation() : void
    {
        $product    = $this->getTestProduct([]);
        self::assertCount(2, $product->compositions);

        $composition                = new Composition( new Material('test'), 30);
        $composition->product       = $product;
        $product->compositions[]    = $composition;


        $em     = $this->em(['postPersist', 'postFlush']);
        $em->persist($composition);
        $em->flush();

        $product    = $this->find($product->id, $product::class);
        self::assertNotNull($product);

        $productSr  = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);

        self::assertCount(3, $product->compositions);
        self::assertProductsEquals($product, $productSr);
    }

    public function testRemoveRelation() : void
    {
        $product    = $this->getTestProduct();
        $em         = $this->em(['preRemove', 'postRemove', 'postFlush']);

        $product->compositions->remove(0);

        $em->flush();
        $em->clear();

        $product    = $this->find($product->id, $product::class);
        self::assertNotNull($product);

        $productSr  = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);

        self::assertCount(1, $product->compositions);


        self::assertProductsEquals($product, $productSr);
    }

    public function testSyncNoneCollection() : void
    {
        $this->checkIndex();

        $em         = $this->em(['postPersist', 'postFlush']);
        $product    = new ProductNonSync(Pattern::Floral);
        $em->persist($product);
        $em->flush();

        $productSr = $this->searchById($product->id, $product::class);
        self::assertNull($productSr);

        $this->indexer()->persist($product);
        $this->indexer()->flush();

        $productSr = $this->searchById($product->id, $product::class);

        self::assertNotNull($productSr);
    }

    public function testNonSyncRelation() : void
    {
        $this->checkIndex();

        $material1 = new MaterialNonSync( 1,'denim');
        $material2 = new MaterialNonSync(2,'cotton');

        $product    = new ProductRelationsNonSync(
            [new CompositionNonSync(1, $material1, 30), new CompositionNonSync(2, $material2, 70)],
            new Properties('test_name', 'test_value')
        );

        $this->em()->persist($material1);
        $this->em()->persist($material2);
        $this->em()->persist($product);
        $this->em()->flush();

        self::assertNotNull($product->compositions[0]);
        self::assertNotNull($product->compositions[1]);
        self::assertNotNull($product->properties);

        $this->indexer()->persist($product);
        $this->indexer()->flush();

        $productSr = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);
        self::assertCount(2, $productSr->compositions);
        self::assertNotNull($productSr->compositions[0]);
        self::assertNotNull($productSr->compositions[1]);
        self::assertNotNull($productSr->properties);


        self::assertEquals($product->id, $productSr->id);
        self::assertNotNull($product->compositions[0]);
        self::assertEquals($product->compositions[0]->id, $productSr->compositions[0]->id);
        self::assertEquals($product->compositions[0]->value, $productSr->compositions[0]->value);
        self::assertEquals($product->compositions[0]->material->name, $productSr->compositions[0]->material->name);

        self::assertNotNull($product->compositions[1]);
        self::assertEquals($product->compositions[1]->id, $productSr->compositions[1]->id);
        self::assertEquals($product->compositions[1]->value, $productSr->compositions[1]->value);
        self::assertEquals($product->compositions[1]->material->name, $productSr->compositions[1]->material->name);

        self::assertNotNull($productSr->properties);
        self::assertNotNull($product->properties);
        self::assertEquals($product->properties->name, $productSr->properties->name);
        self::assertEquals($product->properties->value, $productSr->properties->value);


        $material   = $product->compositions[1]->material;
        self::assertEquals('cotton', $material->name);

        $material->name = 'silk';
        $this->em()->persist($material);
        $this->em()->flush();

        $product    = $this->find($product->id, ProductRelationsNonSync::class);
        self::assertNotNull($product);
        self::assertNotNull($product->compositions[1]);

        self::assertEquals('silk', $product->compositions[1]->material->name);

        $productSr = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);
        self::assertNotNull($productSr->compositions[1]);
        self::assertEquals('cotton', $productSr->compositions[1]->material->name);


        $product->compositions->remove(0);

        $this->em()->flush();
        $this->em()->clear();

        $product    = $this->find($product->id, ProductRelationsNonSync::class);
        self::assertNotNull($product);
        self::assertCount(1, $product->compositions);

        $productSr = $this->searchById($product->id, $product::class);
        self::assertNotNull($productSr);
        self::assertCount(2, $productSr->compositions);
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


    /**
     * @param string[] $allowed
     * @return EntityManagerInterface
     */
    protected function em(array $allowed = []) : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em         = self::getContainer()->get(EntityManagerInterface::class);

        $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
        foreach ($events as $event) {
            $searched   = array_find($em->getEventManager()->getListeners($event), fn($ls) => $ls instanceof EventListener);

            if(in_array($event, $allowed, true)) {
                if($searched === null) {
                    $em->getEventManager()->addEventListener($event, $this->listener());
                }
            }
            else {
                if($searched !== null) {
                    $em->getEventManager()->removeEventListener($event, $searched);
                }
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


    /**
     * @param array<string> $events
     * @return Product
     */
    protected function getTestProduct(array $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush']) : Product
    {
        $product    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition(new Material( 'denim'), 30), new Composition( new Material('cotton'), 70)],
            new Properties( 'test_name', 'test_value')
        );


        $em     = $this->em($events);;
        $em->persist($product);
        $em->flush();

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
     * @throws ORMException
     * @throws OptimisticLockException
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

    protected function listener() : EventListener
    {
        /** @var EventListener $indexer */
        $indexer   = self::getContainer()->get(EventListener::class);

        return $indexer;
    }

    protected function indexer() : Indexer
    {
        /** @var Indexer $indexer */
        $indexer   = self::getContainer()->get(Indexer::class);

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