<?php

namespace DvsaDoctrineModuleTest\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use DvsaDoctrineModule\Factory\Cache\DoctrineMemcacheCacheFactory;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DoctrineMemcacheCacheFactoryTest extends TestCase
{
    /**
     * @var bool|null
     */
    protected $backupStaticAttributes;

    /**
     * @var bool|null
     */
    protected $runTestInSeparateProcess;

    public function setUp(): void
    {
        if (!class_exists('Memcache')) {
            $this->markTestSkipped('Memcache needs to be installed to run this test');
        }
    }

    /**
     * @return void
     */
    public function testItIsAZendFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new DoctrineMemcacheCacheFactory());
    }

    /**
     * @return void
     */
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

        $this->assertInstanceOf(Cache::class, $service);
    }

    /**
     * @return void
     */
    public function testItCreatesTheServiceWithDefaults()
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcache' => []
            ]
        ]);

        $service = (new DoctrineMemcacheCacheFactory())->create($serviceManager);

        $this->assertInstanceOf(Cache::class, $service);
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
