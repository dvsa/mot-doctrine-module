<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;

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
     */
    public function create(ContainerInterface $serviceLocator): \Doctrine\Common\Cache\Cache
    {
        $config = $this->getMemcacheConfig($serviceLocator);

        $memcached = MemcachedAdapter::createConnection(
            $this->getServers($config['servers'])
        );

        $cache = new MemcachedAdapter($memcached);

        return DoctrineProvider::wrap($cache);
    }

    private function getServers(array $servers): array
    {
        $serverList = [];
        foreach ($servers as $server) {
            $server = $this->normalizeMemcacheServerConfig($server);
            $serverList[] = [$server['host'], $server['port'], $server['weight']];
        }
        return $serverList;
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

        $server['persistent'] = isset($server['persistent']) ? $server['persistent'] : true;
        $server['weight'] = isset($server['weight']) ? $server['weight'] : 1;

        return $server;
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Cache
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): Cache
    {
        return $this->create($container);
    }
}
