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
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Cache */
        return $serviceLocator->get($this->getConfiguredServiceName($serviceLocator));
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return string
     */
    private function getConfiguredServiceName(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (
            is_array($config) &&
            isset($config['cache']) &&
            isset($config['cache']['instance']) &&
            is_string($config['cache']['instance'])
        ) {
            return $config['cache']['instance'];
        }

        throw new \InvalidArgumentException('No cache driver was configured');
    }

    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        if ($container instanceof ServiceLocatorInterface) {
            return $this->create($container);
        }

        throw new \InvalidArgumentException('$container is of incorrect type');
    }
}
