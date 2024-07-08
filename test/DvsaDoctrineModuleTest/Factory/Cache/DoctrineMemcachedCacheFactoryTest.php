<?php

namespace DvsaDoctrineModuleTest\Factory\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use DvsaDoctrineModule\Factory\Cache\DoctrineMemcachedCacheFactory;
use Error;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

class DoctrineMemcachedCacheFactoryTest extends TestCase
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
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached needs to be installed to run this test');
        }
    }

    /**
     * @return void
     */
    public function testItIsAZendFactory(): void
    {
        $this->assertInstanceOf(FactoryInterface::class, new DoctrineMemcachedCacheFactory());
    }

    /**
     * @return void
     */
    public function testItCreatesTheMemcachedCache(): void
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcached' => [
                    'servers' => [['host' => '127.0.0.1', 'port' => 11211]],
                    'options' => [\Memcached::OPT_HASH => \Memcached::HASH_DEFAULT],
                    'persistent_id' => null,
                ]
            ]
        ]);

        $factory = new DoctrineMemcachedCacheFactory();
        $service = $factory->create($serviceManager);

        $this->assertInstanceOf(DoctrineProvider::class, $service);

        // Validate the Memcached configuration
        $memcachedAdapter = $factory->createMemcachedAdapter($serviceManager);
        $reflectionClass = new \ReflectionClass(MemcachedAdapter::class);
        $reflectionProperty = $reflectionClass->getProperty('client');
        $memcached = $reflectionProperty->getValue($memcachedAdapter);
        $this->assertInstanceOf(\Memcached::class, $memcached);

        if (!($memcached instanceof \Memcached)) {
            throw new Error('Memcached is null');
        }

        $this->assertSame([['host' => '127.0.0.1', 'port' => 11211, 'type' => 'TCP']], $memcached->getServerList());
    }

    /**
     * @return void
     */
    public function testItCreatesTheServiceWithDefaults(): void
    {
        $serviceManager = $this->getServiceManager([
            'cache' => [
                'memcached' => ['persistent_id' => null]
            ]
        ]);

        $factory = new DoctrineMemcachedCacheFactory();
        $service = $factory->create($serviceManager);

        $this->assertInstanceOf(Cache::class, $service);

        // Validate the Memcached configuration
        $memcachedAdapter = $factory->createMemcachedAdapter($serviceManager);
        $reflectionClass = new \ReflectionClass(MemcachedAdapter::class);
        $reflectionProperty = $reflectionClass->getProperty('client');
        $memcached = $reflectionProperty->getValue($memcachedAdapter);

        $this->assertInstanceOf(\Memcached::class, $memcached);

        $this->assertSame([['host' => 'localhost', 'port' => 11211, 'type' => 'TCP']], $memcached->getServerList());
    }

    /**
     * @return ServiceManager&MockObject $serviceManager
     */
    private function getServiceManager(array $config): ServiceManager&MockObject
    {
        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return $serviceManager;
    }
}
