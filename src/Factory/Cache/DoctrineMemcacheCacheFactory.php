<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Doctrine\Common\Cache\MemcacheCache;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DoctrineMemcacheCacheFactory implements FactoryInterface
{
    private $defaults = [
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
     * @return mixed
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
    private function getMemcacheConfig(ContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (isset($config['cache']['memcache']['servers'])) {
            return $config['cache']['memcache'];
        }

        return $this->defaults;
    }

    /**
     * @param array $server
     *
     * @return array
     */
    private function normalizeMemcacheServerConfig($server)
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
     * @param string $name
     * @param array|null $args
     * @return object|void
     */
    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        return $this->create($container);
    }
}
