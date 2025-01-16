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
use Maratzhe\SymfonyTypesense\Factory\FinderFactory;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\Finder;
use Maratzhe\SymfonyTypesense\Service\EventListener;
use Maratzhe\SymfonyTypesense\Service\Indexer;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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
        $this->assertNotEmpty($productSr);
        $this->assertProductsEquals($product, $productSr);
    }


    public function testRemove() : void
    {
        $em         = $this->em(['preRemove', 'postRemove', 'postFlush']);
        $product    = $this->getTestProduct();
        $id         = $product->id;

        $this->indexer()->persist($product);
        $this->indexer()->flush();


        $productSr  = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);

        $em->remove($product);
        $em->flush();

        $productDb  = $this->find($id, $product::class);
        $this->assertNull($productDb);

        $productSr  = $this->searchById($id, $product::class);
        $this->assertNull($productSr);
    }

    public function testEdit() : void
    {
        $product    = $this->getTestProduct();
        $em         = $this->em(['postUpdate', 'postFlush']);
        $this->assertEquals(Pattern::Animal, $product->pattern);

        $product->pattern = Pattern::Camouflage;
        $em->persist($product);
        $em->flush();

        $productSr = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);
        $this->assertProductsEquals($product, $productSr);
    }

    public function testEditRelation() : void
    {
        $product    = $this->getTestProduct([]);
        $em         = $this->em(['postUpdate', 'postFlush']);

        $this->assertCount(2, $product->compositions);
        $this->assertNotNull($product->compositions[1]);
        $material   = $product->compositions[1]->material;
        $this->assertEquals('cotton', $material->name);


        $material->name = 'silk';
        $em->persist($material);
        $em->flush();


        $product  = $this->find($product->id, $product::class);
        $this->assertNotNull($product);
        $productSr  = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);

        $this->assertNotNull($product->compositions[1]);
        $material = $product->compositions[1]->material;
        $this->assertEquals('silk', $material->name);

        $this->assertProductsEquals($product, $productSr);
    }

    public function testAddRelation() : void
    {
        $product    = $this->getTestProduct([]);
        $this->assertCount(2, $product->compositions);

        $composition                = new Composition( new Material('test'), 30);
        $composition->product       = $product;
        $product->compositions[]    = $composition;


        $em     = $this->em(['postPersist', 'postFlush']);
        $em->persist($composition);
        $em->flush();

        $product    = $this->find($product->id, $product::class);
        $this->assertNotNull($product);

        $productSr  = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);

        $this->assertCount(3, $product->compositions);
        $this->assertProductsEquals($product, $productSr);
    }

    public function testRemoveRelation() : void
    {
        $product    = $this->getTestProduct();
        $em         = $this->em(['preRemove', 'postRemove', 'postFlush']);

        $product->compositions->remove(0);

        $em->flush();
        $em->clear();

        $product    = $this->find($product->id, $product::class);
        $this->assertNotNull($product);

        $productSr  = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);

        $this->assertCount(1, $product->compositions);


        $this->assertProductsEquals($product, $productSr);
    }

    public function testSyncNoneCollection() : void
    {
        $this->checkIndex();

        $em         = $this->em(['postPersist', 'postFlush']);
        $product    = new ProductNonSync(Pattern::Floral);
        $em->persist($product);
        $em->flush();

        $productSr = $this->searchById($product->id, $product::class);
        $this->assertNull($productSr);

        $this->indexer()->persist($product);
        $this->indexer()->flush();

        $productSr = $this->searchById($product->id, $product::class);

        $this->assertNotNull($productSr);
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

        $this->assertNotNull($product->compositions[0]);
        $this->assertNotNull($product->compositions[1]);
        $this->assertNotNull($product->properties);

        $this->indexer()->persist($product);
        $this->indexer()->flush();

        $productSr = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);
        $this->assertCount(2, $productSr->compositions);
        $this->assertNotNull($productSr->compositions[0]);
        $this->assertNotNull($productSr->compositions[1]);
        $this->assertNotNull($productSr->properties);


        $this->assertEquals($product->id, $productSr->id);
        $this->assertNotNull($product->compositions[0]);
        $this->assertEquals($product->compositions[0]->id, $productSr->compositions[0]->id);
        $this->assertEquals($product->compositions[0]->value, $productSr->compositions[0]->value);
        $this->assertEquals($product->compositions[0]->material->name, $productSr->compositions[0]->material->name);

        $this->assertNotNull($product->compositions[1]);
        $this->assertEquals($product->compositions[1]->id, $productSr->compositions[1]->id);
        $this->assertEquals($product->compositions[1]->value, $productSr->compositions[1]->value);
        $this->assertEquals($product->compositions[1]->material->name, $productSr->compositions[1]->material->name);

        $this->assertNotNull($productSr->properties);
        $this->assertNotNull($product->properties);
        $this->assertEquals($product->properties->name, $productSr->properties->name);
        $this->assertEquals($product->properties->value, $productSr->properties->value);


        $material   = $product->compositions[1]->material;
        $this->assertEquals('cotton', $material->name);

        $material->name = 'silk';
        $this->em()->persist($material);
        $this->em()->flush();

        $product    = $this->find($product->id, ProductRelationsNonSync::class);
        $this->assertNotNull($product);
        $this->assertNotNull($product->compositions[1]);

        $this->assertEquals('silk', $product->compositions[1]->material->name);

        $productSr = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);
        $this->assertNotNull($productSr->compositions[1]);
        $this->assertEquals('cotton', $productSr->compositions[1]->material->name);


        $product->compositions->remove(0);

        $this->em()->flush();
        $this->em()->clear();

        $product    = $this->find($product->id, ProductRelationsNonSync::class);
        $this->assertNotNull($product);
        $this->assertCount(1, $product->compositions);

        $productSr = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);
        $this->assertCount(2, $productSr->compositions);
    }

    protected function assertProductsEquals(Product $product, Product $productSr) : void
    {
        $this->assertEquals($product->id, $productSr->id);

        if($product->custom_id !== null) {
            $this->assertNotNull($productSr->custom_id);
            $this->assertTrue($product->custom_id->equals($productSr->custom_id));
        }
        else {
            $this->assertNull($productSr->custom_id);
        }


        $this->assertEquals($product->pattern, $productSr->pattern);

        if($product->price !== null) {
            $this->assertNotNull($productSr->price);
            $this->assertEquals($product->price->price, $productSr->price->price);
            $this->assertEquals($product->price->currency, $productSr->price->currency);
        }
        else {
            $this->assertNull($productSr->price);
        }

        $this->assertCount(count($product->colors), $productSr->colors);
        for($i=0; $i<count($product->colors); $i++){
            $this->assertEquals($product->colors[$i], $productSr->colors[$i]);
        }

        $this->assertCount(count($product->photos), $productSr->photos);
        for($i=0; $i<count($product->photos); $i++){
            $this->assertEquals($product->photos[$i]->size, $productSr->photos[$i]->size);
            $this->assertEquals($product->photos[$i]->url, $productSr->photos[$i]->url);
        }

        $this->assertCount(count($product->compositions), $productSr->compositions);
        for($i=0; $i<count($product->compositions); $i++){
            $this->assertNotNull($productSr->compositions[$i]);
            $this->assertNotNull($product->compositions[$i]);

            $this->assertEquals($product->compositions[$i]->id, $productSr->compositions[$i]->id);
            $this->assertEquals($product->compositions[$i]->material->id, $productSr->compositions[$i]->material->id);
            $this->assertEquals($product->compositions[$i]->material->name, $productSr->compositions[$i]->material->name);
        }

        if($product->properties !== null) {
            $this->assertNotNull($productSr->properties);
            $this->assertEquals($product->properties->id, $productSr->properties->id);
            $this->assertEquals($product->properties->name, $productSr->properties->name);
            $this->assertEquals($product->properties->value, $productSr->properties->value);
        }
        else {
            $this->assertNull($productSr->properties);
        }
    }


    /**
     * @param string[] $allowed
     * @return EntityManagerInterface
     */
    protected function em(array $allowed = []) : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em         = $this->getContainer()->get(EntityManagerInterface::class);

        $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
        foreach ($events as $event) {
            $searched   = array_find($em->getEventManager()->getListeners($event), fn($ls) => $ls instanceof EventListener);

            if(in_array($event, $allowed)) {
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
        $manager    = $this->getContainer()->get(CollectionManager::class);

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
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    protected function searchById(?int $id, string $finder) : object|null
    {
        $result = $this->finder($finder)->query()->filterBy('id :=' . $id)->getResult();

        if(empty($result->hits)) {
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
        $factory    = $this->getContainer()->get(FinderFactory::class);

        return $factory->create($finder);
    }

    protected function listener() : EventListener
    {
        /** @var EventListener $indexer */
        $indexer   = $this->getContainer()->get(EventListener::class);

        return $indexer;
    }

    protected function indexer() : Indexer
    {
        /** @var Indexer $indexer */
        $indexer   = $this->getContainer()->get(Indexer::class);

        return $indexer;
    }

    protected function manager() : CollectionManager
    {
        /** @var CollectionManager $manager */
        $manager   = $this->getContainer()->get(CollectionManager::class);

        return $manager;
    }

    protected function transformer() : Transformer
    {
        /** @var Transformer $transformer */
        $transformer = $this->getContainer()->get(Transformer::class);

        return $transformer;
    }
}