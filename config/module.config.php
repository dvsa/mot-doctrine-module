<?php

use Doctrine\Common\Cache\Cache;
use DvsaDoctrineModule\Factory\Cache\DoctrineCacheFactory;
use DvsaDoctrineModule\Factory\Cache\DoctrineMemcacheCacheFactory;
use DvsaDoctrineModule\Factory\Cache\DoctrineMemcachedCacheFactory;

return [
    'service_manager' => [
        'factories' => [
            'doctrine.cache.memcache' => DoctrineMemcacheCacheFactory::class,
            'doctrine.cache.memcached' => DoctrineMemcachedCacheFactory::class,
            Cache::class => DoctrineCacheFactory::class,
        ],
    ],
];
