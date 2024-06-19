<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Doctrine\Common\Cache\MemcacheCache;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
     * @return MemcacheCache
     */
    public function create(ContainerInterface $serviceLocator)
    {
        $config = $this->getMemcacheConfig($serviceLocator);

        $memcache = new \Memcache();
        foreach ($config['servers'] as $server) {
            $server = $this->normalizeMemcacheServerConfig($server);
            $memcache->addServer($server['host'], $server['port'], $server['persistent'], $server['weight']);
        }

        $cache = new MemcacheCache();
        $cache->setMemcache($memcache);

        return $cache;
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
            /** @var array */
            return $config['cache']['memcache'];
        }

        return $this->defaults;
    }

    /**
     * @param array $server
     *
     * @return array
     */
    private function normalizeMemcacheServerConfig($server): array
    {
        if (!isset($server['host']) || !isset($server['port'])) {
            throw new \InvalidArgumentException('Memcache server needs a host and a port to be configured');
        }

        $server['persistent'] = $server['persistent'] ?? true;
        $server['weight'] = $server['weight'] ?? 1;

        return $server;
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<string, mixed>|null $options
     * @return MemcacheCache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->create($container);
    }
}
