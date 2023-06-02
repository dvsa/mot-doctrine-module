<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Doctrine\Common\Cache\MemcachedCache;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DoctrineMemcachedCacheFactory implements FactoryInterface
{
    const PERSISTENT_ID = 'MOT';

    private $defaults = [
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
     * @return mixed
     */
    public function create(ContainerInterface $serviceLocator)
    {
        $cache = new MemcachedCache();
        $cache->setMemcached($this->createMemcached($serviceLocator));

        return $cache;
    }

    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return \Memcached
     */
    private function createMemcached(ContainerInterface $serviceLocator)
    {
        $config = $this->getMemcachedConfig($serviceLocator);
        $persistentId = array_key_exists('persistent_id', $config) ? $config['persistent_id'] : self::PERSISTENT_ID;

        $memcached = new \Memcached($persistentId);

        // only add servers to Memcached list if not already present, as this
        // Memcached instance will persist across sessions with identifier 'MOT'
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
     * @return array|object
     */
    private function getMemcachedConfig(ContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (!isset($config['cache']['memcached']['servers'])) {
            $config['cache']['memcached']['servers'] = $this->defaults['servers'];
        }

        return $config['cache']['memcached'];
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
