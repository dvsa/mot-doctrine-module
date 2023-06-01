<?php

namespace DvsaDoctrineModule\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DoctrineCacheFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Cache
     */
    public function create(ContainerInterface $serviceLocator)
    {
        return $serviceLocator->get($this->getConfiguredServiceName($serviceLocator));
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Cache
     */
    private function getConfiguredServiceName(ContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (isset($config['cache']['instance'])) {
            return $config['cache']['instance'];
        }

        throw new \InvalidArgumentException('No cache driver was configured');
    }

    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        return $this->create($container);
    }
}