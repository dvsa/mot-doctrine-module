<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;

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
     */
    public function create(ContainerInterface $serviceLocator): Cache
    {
        $memcachedAdapter = $this->createMemcachedAdapter($serviceLocator);
        return DoctrineProvider::wrap($memcachedAdapter);
    }


    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return MemcachedAdapter
     */
    public function createMemcachedAdapter(ContainerInterface $serviceLocator): MemcachedAdapter
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

        return new MemcachedAdapter($memcached);
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

        /** @var array */
        return $config['cache']['memcached'];
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Cache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Cache
    {
        return $this->create($container);
    }
}
