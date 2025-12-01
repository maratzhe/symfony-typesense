# Symfony Typesense

A [Typesense](https://typesense.org/) integration for the [Symfony](https://symfony.com) web framework. 

Attributes based and completely auto mapping.

Features:

- Attributes based
- Completely auto mapping based on Doctrine types. Manual mapping also available.
- Relations management. Auto update parent entity when child relation was updated.

## Installation


Install the bundle using composer:

```bash
composer require acseo/typesense-bundle
````

```php

<?php
// config/bundles.php

return [
    Maratzhe\SymfonyTypesense\Bundle\TypesenseBundle::class => ['all' => true],
]
```

Configure the Bundle:

```bash
# .env
TYPESENSE_DSN=http://localhost:8108?api_key=xyz
```

### Docker
If you use Symfony with [docker image](https://github.com/dunglas/symfony-docker) add to compose.yaml:

```yaml
  typesense:
    image: typesense/typesense:27.1
    restart: on-failure
    ports:
      - "8108:8108"
    volumes:
      - typesense_data:/data
    command: '--data-dir /data --api-key=xyz --enable-cors'
```

And edit .env file:

```bash
# .env
TYPESENSE_DSN=http://typesense:8108?api_key=xyz
```

## Mapping


### #[SearchCollection]

```php

use Maratzhe\SymfonyTypesense\Enum\SyncMode;
use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;

#[Entity]
#[SearchCollection(name: 'product', sync: SyncMode::AUTO)]
class Product
```

Parameters:

- **name**: string. Collection name in Typesense. **Default**: class name.
- **sync**: SyncMode. Update mode. SyncMode::AUTO - update collection on entity create, update or remove. SyncMode::NONE - update entity only by CLI command. **Default**: SyncMode::NONE.


### #[SearchField]


```php
    use Maratzhe\SymfonyTypesense\Attribute\SearchField;
    use Maratzhe\SymfonyTypesense\Enum\FieldType;
    
    ...
    #[Column(type: 'string', length: '2048')]
    #[SearchField]
    public string $description = '';
    ...
```

Parameters:

- **name**: field name in Typesense. **Default**: *entity field name*.
- **type**: FieldType. [Field type](https://typesense.org/docs/29.0/api/collections.html#field-types) in Typesense. **Default**: null (mapping from Doctrine ORM type).
- **locale**: string. Field [locale](https://typesense.org/docs/29.0/api/collections.html#field-parameters). **Default**: "".
- **optional**: bool. [Optional](https://typesense.org/docs/29.0/api/collections.html#field-parameters) field. **Default**: true.
- **facet**: bool. Enables [faceting](https://typesense.org/docs/29.0/api/collections.html#field-parameters) on the field. **Default**: false.
- **index**: bool. Enables [index](https://typesense.org/docs/29.0/api/collections.html#field-parameters) on the field. **Default**: false.
- **infix**: bool. Enables [infix search](https://typesense.org/docs/29.0/api/collections.html#field-parameters) on the field. **Default**: false.
- **sort**: bool. Enables [sort](https://typesense.org/docs/29.0/api/collections.html#field-parameters) on the field. **Default**: false.
- **stem**: bool. Enables [stem](https://typesense.org/docs/29.0/api/collections.html#field-parameters) on the field. **Default**: false.

### #[SearchRelation]

```php
    use Maratzhe\SymfonyTypesense\Attribute\SearchCollection;
    
    ...
    #[OneToMany(targetEntity: Composition::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[SearchRelation(sync: SyncMode::AUTO, bulk: true)]
    public Collection $compositions;
    ...
```

Parameters:

- **sync**: SyncMode. Update mode. SyncMode::AUTO - update entity on relation create, update or remove. SyncMode::NONE - disable auto update. **Default**: SyncMode::NONE.
- **bulk**: bool. On true - update relation on CLI search:import command. **Default**: false.


## Search
```php
    use Maratzhe\SymfonyTypesense\Factory\FinderFactory;

    final class ProductController extends AbstractController
    {
        #[Route('/product', name: 'product.index')]
        public function index(FinderFactory $factory): Response
        {
            $finder     = $factory->create(Product::class);
            $result     = $finder->query('blue', 'description')
                ->filterBy('published := true')
                ->sortBy('id:desc')
                ->getResult();

            //dump($result);           
            //\Maratzhe\SymfonyTypesense\Search\Result {
            //    +facet_counts: []
            //    +found: 1
            //    +page: 1
            //    +pages: 1
            //    +per_page: 10
            //    +out_of: 110
            //    +search_time_ms: 0
            //    +hits: array:1 [
            //        0 => Maratzhe\SymfonyTypesense\Search\Hit {
            //            +document: App\Entity\Product { ▶}
            //            +highlight: array:1 [ ▶]
            //            +text_match: 578730123365187705
            //            +text_match_info: array:7 [ ▶]
            //        }
            //    ]
            //}
        }
    }
```

## CLI commands

### search:create {index}

Create Typesense collection.

Arguments:

- **index**: index name.

Options:

- **all**: create indexes for all entities.

### search:import {index}

Import collection from database to Typesense.

Arguments:

- **index**: index name.

Options:

- **all**: import all collections.
- **first-page**: page to start population from. **Default**: 1
- **last-page**: page to end population on. **Default**: null
- **per-page**: entities per page. **Default**: 1000


### search:mapping {index}

Show collection mapping.

Arguments:

- **index**: index name.

Options:

- **all**: show mapping for all collections.
- **real**: show mapping from Typesense instead of generated.


### search:show {index} {id}

Show document from Typesense.

Arguments:

- **index**: index name.
- **id**: document ID.


## Example

```php
    #[Entity]
    #[SearchCollection(name: 'product', sync: SyncMode::AUTO)]
    class Product
    {
        #[Id]
        #[Column]
        #[GeneratedValue]
        //ID of entity mapping by default
        public ?int $id = null;
    
        #[Column(type: 'custom_id', nullable: true)]
        #[SearchField]
        //Use Doctrine mapping custom type
        public ?CustomId $custom_id;
    
        /** @var array<int, Color>  */
        #[Column(type: 'color_array')]
        #[SearchField(name: 'colors', type: FieldType::INT32_ARRAY, facet: true, index: true)]
        public array $colors;
    
        /** @var array<int, Photo>  */
        #[Column(type: 'photo_array')]
        #[SearchField(name: 'photos.*', type: FieldType::OBJECT_ARRAY)]
        #[SearchField(name: 'photos.*.size', type: FieldType::INT32)]
        #[SearchField(name: 'photos.*.url', type: FieldType::STRING)]
        //Complex manual mapping for array of objects
        public array $photos;
    
        #[Column(nullable: true)]
        #[SearchField(facet:true, index: true)]
        public ?Pattern $pattern;
    
        #[Embedded(class: Price::class)]
        //for embedded objects mapping must be in embedded class. 
        public ?Price $price;
    
        /** @var Collection<int, Composition> $compositions  */
        #[OneToMany(targetEntity: Composition::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
        #[SearchRelation(sync: SyncMode::AUTO, bulk: true)]
        //When child relation was updated this entity will update too. Also all relations will be updated by calling "search:import"
        public Collection $compositions;
    
        #[OneToOne(targetEntity: Properties::class, cascade: ['all'], orphanRemoval: true)]
        #[SearchRelation(bulk: true)]
        //No update after child relation updated. Update only by CLI command.
        public ?Properties $properties;
    
        #[Column]
        #[SearchField(index: true)]
        public bool $published;
    
        #[Column]
        #[SearchField(index: true)]
        public string $description;
    
        public function __construct(
            ?CustomId $custom_id = null,
            array $colors = [],
            array $photos = [],
            ?Pattern $pattern = null,
            ?Price $price = null,
            array $compositions = [],
            ?Properties $properties = null,
            bool $published = false,
            string $description = ''
        )
        {
            $this->custom_id        = $custom_id;
            $this->colors           = $colors;
            $this->photos           = $photos;
            $this->compositions     = new ArrayCollection($compositions);
            $this->pattern          = $pattern;
            $this->price            = $price;
            $this->properties       = $properties;
            $this->published        = $published;
            $this->description      = $description;
    
            foreach ($this->compositions as $composition) {
                $composition->product = $this;
            }
        }
    }
```


Mapping for this class:

```bash
➜  :./bin/console search:mapping product

Mapping of product (class: App\Entity\Product), generated
╔══════════════════════════════╤══════════╤════════╤══════════╤═══════╤═══════╤═══════╤══════╤══════╗
║ name                         │ type     │ locale │ optional │ facet │ index │ infix │ sort │ stem ║
╠══════════════════════════════╪══════════╪════════╪══════════╪═══════╪═══════╪═══════╪══════╪══════╣
║ id                           │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ custom_id                    │ string   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ colors                       │ int32[]  │        │ yes      │ yes   │ yes   │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ photos.*                     │ object[] │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ photos.*.size                │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ photos.*.url                 │ string   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ pattern                      │ string   │        │ yes      │ yes   │ yes   │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ price.price                  │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ price.currency               │ string   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ published                    │ bool     │        │ yes      │ no    │ yes   │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ description                  │ string   │        │ yes      │ no    │ yes   │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ compositions.*               │ object[] │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ compositions.*.id            │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ compositions.*.value         │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ compositions.*.material      │ object   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ compositions.*.material.id   │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ compositions.*.material.name │ string   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ properties                   │ object   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ properties.id                │ int32    │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ properties.name              │ string   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╟──────────────────────────────┼──────────┼────────┼──────────┼───────┼───────┼───────┼──────┼──────╢
║ properties.value             │ string   │        │ yes      │ no    │ no    │ no    │ no   │ no   ║
╚══════════════════════════════╧══════════╧════════╧══════════╧═══════╧═══════╧═══════╧══════╧══════╝
```