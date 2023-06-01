# DVSA Doctrine Module

This module provides additional doctrine tools and configurations for doctrine
(including doctrine/cache, DoctrineModule and DoctrineORMModule).

## Cache

### Cache service alias

A service factory is provided for the `Doctrine\Common\Cache\Cache` service:

    use Doctrine\Common\Cache\Cache;

    $cache = $serviceLocator->get(Cache::class);

The service acts as an alias to a specific implementation, which needs to be configured:

    return [
        'cache' => [
            'instance' => 'doctrine.cache.memcached'
        ]
    ]

In this case the `doctrine.cache.memcached` is the service name that will be used.

### Memcache

A service factory is provided for the `doctrine.cache.memcache` service, which doctrine expects
if **memcache** driver is chosen for caching.

It can be configured with the following details (these are defaults):

    return [
        'cache' => [
            'memcache' => [
                'servers' => [
                    [
                        'host' => 'localhost',
                        'port' => 11211,
                        'persistent' => true,
                        'weight' => 1
                    ],
                ],
            ],
        ],
    ]


### Memcached

A service factory is provided for the `doctrine.cache.memcached` service, which doctrine expects
if **memcached** driver is chosen for caching.

It can be configured with the following details (these are defaults):

    return [
        'cache' => [
            'memcached' => [
                'servers' => [
                    [
                        'host' => 'localhost',
                        'port' => 11211,
                    ],
                ],
                'options' => [
                    \Memcached::OPT_HASH => \Memcached::HASH_DEFAULT,
                ],
                'persistent_id' => 'MOT',
            ],
        ],
    ]

All the parameters accepted by [Memcached::addServers](http://php.net/manual/en/memcached.addservers.php)
and [Memcached::setOptions](http://php.net/manual/en/memcached.setoptions.php) are accepted.
The order in servers is significant (keys are ignored).

By default the Memcached instances are destroyed at the end of the request. To create an instance that persists between
requests, use `persistent_id` to specify a unique ID for the instance. All instances created with the same `persistent_id`
will share the same connection. To disable persistence set `persistent_id` to `null` (it's enabled by default).

**Warning**: Memcached won't complain if it cannot connect to the server. Application will simply not cache
if wrong parameters were given in the configuration.

