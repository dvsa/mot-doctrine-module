<?php

namespace DvsaDoctrineModuleTest\Factory\Cache;

use DvsaDoctrineModule\Factory\Cache\DoctrineMemcacheCacheFactory;
use Doctrine\Common\Cache\MemcacheCache;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DoctrineMemcacheCacheFactoryTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists('Memcache')) {
            $this->markTestSkipped('Memcache needs to be installed to run this test');
        }
    }

    public function testItIsAZendFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new DoctrineMemcacheCacheFactory());
    }

    public function testItCreatesTheMemcacheCache()
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcache' => [
                    'servers' => [['host' => 'localhost', 'port' => 11211, 'type' => 'TCP']]
                ]
            ]
        ]);

        $service = (new DoctrineMemcacheCacheFactory())->create($serviceManager);

        $this->assertInstanceOf(MemcacheCache::class, $service);
    }

    public function testItCreatesTheServiceWithDefaults()
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcache' => []
            ]
        ]);

        $service = (new DoctrineMemcacheCacheFactory())->create($serviceManager);

        $this->assertInstanceOf(MemcacheCache::class, $service);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceManager(array $config)
    {
        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return $serviceManager;
    }
}
