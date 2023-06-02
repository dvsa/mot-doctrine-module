<?php

namespace DvsaDoctrineModuleTest\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use DvsaDoctrineModule\Factory\Cache\DoctrineCacheFactory;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;

class DoctrineCacheFactoryTest extends TestCase
{
    public function testItIsAZendFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new DoctrineCacheFactory());
    }

    public function testItReturnsTheConfiguredCache()
    {
        $serviceManager = $this->getServiceManager([
            'config' => [
                'cache' => ['instance' => 'doctrine.cache.filesystem']
            ],
            'doctrine.cache.filesystem' => $filesystemCache = $this->createMock(Cache::class),
        ]);

        $cache = (new DoctrineCacheFactory())->create($serviceManager);

        $this->assertSame($filesystemCache, $cache);
    }

    public function testItThrowsAnExceptionIfCacheIsNotConfigured()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No cache driver was configured");

        $serviceManager = $this->getServiceManager([
            'config' => [],
            'doctrine.cache.filesystem' => $this->createMock(Cache::class),
        ]);

        (new DoctrineCacheFactory())->create($serviceManager);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceManager(array $services)
    {
        $serviceManager = $this->createMock(ServiceManager::class);

        $serviceManager->expects($this->any())
            ->method('get')
            ->with(call_user_func_array([$this, 'logicalOr'], array_keys($services)))
            ->will($this->returnCallback(function ($serviceName) use ($services) {
                return $services[$serviceName];
            }));

        return $serviceManager;
    }
}