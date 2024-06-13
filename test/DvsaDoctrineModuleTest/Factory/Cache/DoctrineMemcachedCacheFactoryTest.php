<?php

namespace DvsaDoctrineModuleTest\Factory\Cache;

use Doctrine\Common\Cache\MemcachedCache;
use DvsaDoctrineModule\Factory\Cache\DoctrineMemcachedCacheFactory;
use Error;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;

class DoctrineMemcachedCacheFactoryTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached needs to be installed to run this test');
        }
    }

    /**
     * @return void
     */
    public function testItIsAZendFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new DoctrineMemcachedCacheFactory());
    }

    /**
     * @return void
     */
    public function testItCreatesTheMemcachedCache()
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcached' => [
                    'servers' => [['host' => '127.0.0.1', 'port' => 11222]],
                    'options' => [\Memcached::OPT_HASH => \Memcached::HASH_DEFAULT],
                    'persistent_id' => null,
                ]
            ]
        ]);

        $service = (new DoctrineMemcachedCacheFactory())->create($serviceManager);

        $this->assertInstanceOf(MemcachedCache::class, $service);

        if (!($service->getMemcached() instanceof \Memcached)) {
            throw new Error('Memcahced is null');
        }

        $this->assertSame([['host' => '127.0.0.1', 'port' => 11222, 'type' => 'TCP']], $service->getMemcached()->getServerList());
    }

    /**
     * @return void
     */
    public function testItCreatesTheServiceWithDefaults()
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcached' => ['persistent_id' => null]
            ]
        ]);

        $service = (new DoctrineMemcachedCacheFactory())->create($serviceManager);

        $this->assertInstanceOf(MemcachedCache::class, $service);

        if (!($service->getMemcached() instanceof \Memcached)) {
            throw new Error('Memcahced is null');
        }

        $this->assertSame([['host' => 'localhost', 'port' => 11211, 'type' => 'TCP']], $service->getMemcached()->getServerList());
    }

    /**
     * @return \Laminas\ServiceManager\ServiceManager&\PHPUnit\Framework\MockObject\MockObject $serviceManager
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
