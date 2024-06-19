<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

class DoctrineMemcacheCacheFactory implements FactoryInterface
{
    private array $defaults = [
        'servers' => [
            [
                'host' => 'localhost',
                'port' => 11211,
                'persistent' => true,
                'weight' => 1,
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
        $config = $this->getMemcacheConfig($serviceLocator);

        $memcached = new \Memcached();
        foreach ($config['servers'] as $server) {
            $server = $this->normalizeMemcacheServerConfig($server);
            $memcached->addServer($server['host'], $server['port'], $server['weight']);
        }

        return new MemcachedAdapter($memcached);
    }

    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return array
     */
    private function getMemcacheConfig(ContainerInterface $serviceLocator): array
    {
        $config = $serviceLocator->get('config');

        if (
            is_array($config) &&
            isset($config['cache']) &&
            isset($config['cache']['memcache']) &&
            isset($config['cache']['memcache']['servers'])
        ) {
            return $config['cache']['memcache'];
        }

        return $this->defaults;
    }

    /**
     * @param array $server
     *
     * @return array
     */
    private function normalizeMemcacheServerConfig(array $server): array
    {
        if (!isset($server['host']) || !isset($server['port'])) {
            throw new \InvalidArgumentException('Memcache server needs a host and a port to be configured');
        }

        $server['weight'] = $server['weight'] ?? 1;

        return $server;
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return CacheItemPoolInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CacheItemPoolInterface
    {
        return $this->create($container);
    }
}
