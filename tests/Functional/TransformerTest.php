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
use Maratzhe\SymfonyTypesense\Service\EventListener;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class TransformerTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testNormalize(): void
    {
        /**
         * @var Transformer $transformer
         */
        $transformer    = self::getContainer()->get(Transformer::class);
        $product        = $this->testProduct();
        $data           = $transformer->normalize($product);
        $expected       = $this->testData();

        self::assertEquals($expected, $data);
    }

    public function testHydrate(): void
    {
        /**
         * @var Transformer $transformer
         */
        $transformer    = self::getContainer()->get(Transformer::class);
        $product        = $this->testProduct();
        $data           = $this->testData();
        $product2       = $transformer->hydrate(Product::class, $data);

        self::assertEquals($product->id, $product2->id);
        self::assertNotNull($product->custom_id);
        self::assertNotNull($product2->custom_id);
        self::assertTrue($product->custom_id->equals($product2->custom_id));
        self::assertNotNull($product->price);
        self::assertNotNull($product2->price);
        self::assertEquals($product->price->price, $product2->price->price);
        self::assertEquals($product->price->currency, $product2->price->currency);
        self::assertEquals($product->colors, $product2->colors);
        self::assertEquals($product->photos, $product2->photos);
        self::assertEquals($product->pattern, $product2->pattern);

        self::assertCount(2, $product2->compositions);


        for($i=0; $i<count($product->compositions); $i++) {
            self::assertNotNull($product->compositions[$i]);
            self::assertNotNull($product2->compositions[$i]);

            self::assertEquals($product->compositions[$i]->id, $product2->compositions[$i]->id);
            self::assertEquals($product->compositions[$i]->value, $product2->compositions[$i]->value);
            self::assertEquals($product->compositions[$i]->material->id, $product2->compositions[$i]->material->id);
            self::assertEquals($product->compositions[$i]->material->name, $product2->compositions[$i]->material->name);
        }

        self::assertNotNull($product->properties);
        self::assertNotNull($product2->properties);
        self::assertEquals($product->properties->id, $product2->properties->id);
        self::assertEquals($product->properties->value, $product2->properties->value);
        self::assertEquals($product->properties->name, $product2->properties->name);
    }

    protected function testProduct() : Product
    {
        $product   = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(32, 'eur'),
            [new Composition(new Material( 'denim'), 30), new Composition(new Material('cotton'), 70)],
            new Properties( 'dsd', '4343')
        );

        $this->em()->persist($product);
        $this->em()->flush();

        return $product;
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

    /**
     * @return array<string, int|string|float|bool|null|array<int, mixed>>
     */
    protected function testData() : array
    {
        return [
            'id'    => '1',
            'custom_id' => '01944071-3781-70e3-89aa-f00b80fd401d',
            'colors' => [0,2],
            'photos' => [['size' => 100, 'url' => 'test_url']],
            'pattern' => 'animal',
            'price.price'   => 32,
            'price.currency' => 'eur',
            'published' => 0,
            'compositions.0.id' => 1,
            'compositions.0.value' => 30,
            'compositions.0.material.id' => 1,
            'compositions.0.material.name' => 'denim',
            'compositions.1.id' => 2,
            'compositions.1.value' => 70,
            'compositions.1.material.id' => 2,
            'compositions.1.material.name' => 'cotton',
            'properties.id' => 1,
            'properties.name' => 'dsd',
            'properties.value' => '4343',
        ];
    }
}