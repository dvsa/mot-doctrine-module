<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DoctrineMemcachedCacheFactory implements FactoryInterface
{
    private const PERSISTENT_ID = 'MOT';

    private array $defaults = [
        'servers' => [
            [
                'host' => 'localhost',
                'port' => 11211,
            ],
        ],
    ];

    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return CacheItemPoolInterface
     */
    public function create(ContainerInterface $serviceLocator): CacheItemPoolInterface
    {
        return new MemcachedAdapter(
            $this->createMemcached($serviceLocator),
        );
    }

    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return \Memcached
     */
    private function createMemcached(ContainerInterface $serviceLocator): \Memcached
    {
        $config = $this->getMemcachedConfig($serviceLocator);
        $persistentId = array_key_exists('persistent_id', $config) ? $config['persistent_id'] : self::PERSISTENT_ID;

        $memcached = new \Memcached($persistentId);

        // only add servers to Memcached list if not already present
        if (!count($memcached->getServerList())) {
            $memcached->addServers($config['servers']);

            if (isset($config['options']) && is_array($config['options'])) {
                $memcached->setOptions($config['options']);
            }
        }

        return $memcached;
    }

    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return array
     */
    private function getMemcachedConfig(ContainerInterface $serviceLocator): array
    {
        $config = $serviceLocator->get('config');
        if (!is_array($config)) {
            $config = [];
        }

        $config['cache'] = $config['cache'] ?? [];
        $config['cache']['memcached'] = $config['cache']['memcached'] ?? [];
        $config['cache']['memcached']['servers'] = $config['cache']['memcached']['servers'] ?? $this->defaults['servers'];

        return $config['cache']['memcached'];
    }

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param array|null $options
     * @return CacheItemPoolInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null): CacheItemPoolInterface
    {
        return $this->create($container);
    }
}
