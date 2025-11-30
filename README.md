# Symfony Typesense

A [Typesense](https://typesense.org/) integration for the [Symfony](https://symfony.com) web framework. 

Attributes based and completely auto mapping.

Features:

- Attributes based
- Completely auto mapping based on Doctrine types. Manual mapping also available.
- Relations management. Auto update parent entity when child relation was updated.

## Installation


Install the bundle using composer

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

Configure the Bundle

```bash
# .env
TYPESENSE_DSN=http://localhost:8108?api_key=xyz
```

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