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