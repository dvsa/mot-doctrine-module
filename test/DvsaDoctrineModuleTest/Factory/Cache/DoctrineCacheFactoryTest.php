<?php

namespace DvsaDoctrineModuleTest\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use DvsaDoctrineModule\Factory\Cache\DoctrineCacheFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;

class DoctrineCacheFactoryTest extends TestCase
{
    /**
     * @var bool|null
     */
    protected $backupStaticAttributes;

    /**
     * @var bool|null
     */
    protected $runTestInSeparateProcess;

    /**
     * @return void
     */
    public function testItIsAZendFactory(): void
    {
        $this->assertInstanceOf(FactoryInterface::class, new DoctrineCacheFactory());
    }

    /**
     * @return void
     */
    public function testItReturnsTheConfiguredCache(): void
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

    /**
     * @return void
     */
    public function testItThrowsAnExceptionIfCacheIsNotConfigured(): void
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
     * @return ServiceManager&MockObject
     */
    private function getServiceManager(array $services): ServiceManager&MockObject
    {
        $serviceManager = $this->createMock(ServiceManager::class);

        $serviceManager->expects($this->any())
            ->method('get')
            ->with(call_user_func_array([$this, 'logicalOr'], array_keys($services)))
            ->will($this->returnCallback(function (string $serviceName) use ($services): mixed {
                return $services[$serviceName];
            }));

        return $serviceManager;
    }
}
