<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class TypesenseExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        /** @var array{paths: string[], naming_strategy: string, dsn: string|null} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $clientFactory = $container->getDefinition('search.client');
        $clientFactory->setArgument('$dsn', $config['dsn']);
    }

//    public function prepend(ContainerBuilder $container): void
//    {
//        $orm = $container->getExtensionConfig('doctrine')[0]['orm'];
//        if(!is_array($orm) || !isset($orm['mappings']) || !is_array($orm['mappings'])) {
//            return;
//        }
//
//        $naming = isset($orm['naming_strategy']) && is_string($orm['naming_strategy']) ? $orm['naming_strategy'] : null;
//
//
//        $dirs = array_map(function (mixed $data) {
//            if(is_array($data) && isset($data['dir']) && is_string($data['dir'])) {
//                return $data['dir'];
//            }
//
//            return '';
//        } , array_values($orm['mappings']));
//
//        $container->prependExtensionConfig('search', [
//            'paths' => $dirs,
//            'naming_strategy' => $naming
//        ]);
//    }
//
//    /**
//     * @param array{paths: string[], naming_strategy: string, dsn: string|null} $config
//     * @return array{classes: array<string, ClassMeta>, collections: array<string, array{name:string, enable_nested_fields: bool, fields: array<int, array<string, string|bool|float|int>>}>}
//     * @throws \Doctrine\DBAL\Exception
//     */
//    protected function getMapping(ContainerBuilder $container, array $config): array
//    {
//        $dir  = $container->getParameter('kernel.cache_dir');
//
//        if(!is_string($dir)) {
//            throw new \Exception('cache dir not found');
//        }
//
//        $strategy   = match($config['naming_strategy']) {
//            'doctrine.orm.naming_strategy.underscore_number_aware'  => new UnderscoreNamingStrategy(CASE_LOWER),
//            'doctrine.orm.naming_strategy.underscore'   => new UnderscoreNamingStrategy(),
//            default => new DefaultNamingStrategy()
//        };
//
//        $orm = ORMSetup::createAttributeMetadataConfiguration(
//            paths: $config['paths'],
//            isDevMode: true,
//        );
//        $orm->setNamingStrategy($strategy);
//
//        $connection = DriverManager::getConnection([
//            'driver' => 'pdo_sqlite',
//            'path' => $dir.'/db.sqlite',
//        ], $orm);
//
//        $entityManager = new EntityManager($connection, $orm);
//
//        $classes        = Mapper::mapClasses($entityManager);
//        $collections    = Mapper::mapCollections($classes);
//
//        return ['classes' => $classes, 'collections' => $collections];
//    }
}
