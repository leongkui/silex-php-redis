<?php

namespace SilexPhpRedis;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class PhpRedisProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['redis'] = function () use ($app) {
            $thisRedis = new \Redis();
            $host = isset($app['redis.host']) ? $app['redis.host'] : array();
            $port = isset($app['redis.port']) && is_int($app['redis.port']) ? $app['redis.port'] : 6379;
            $timeout = isset($app['redis.timeout']) && is_int($app['redis.timeout']) ? $app['redis.timeout'] : 0;
            $persistent = isset($app['redis.persistent']) ? $app['redis.persistent'] : false;
            $auth = isset($app['redis.auth']) ? $app['redis.auth'] : null;
            $serializerIgbinary = isset($app['redis.serializer.igbinary']) ? $app['redis.serializer.igbinary'] : false;
            $serializerPhp = isset($app['redis.serializer.php']) ? $app['redis.serializer.php'] : false;
            $prefix = isset($app['redis.prefix']) ? $app['redis.prefix'] : null;
            $database = isset($app['redis.database']) ? $app['redis.database'] : null;

            if ($persistent) {
                $thisRedis->pconnect($host, $port, $timeout);
            } else {
                $thisRedis->connect($host, $port, $timeout);
            }

            if (!empty($auth)) {
                $thisRedis->auth($auth);
            }

            if ($database) {
                $thisRedis->select($database);
            }

            if ($serializerIgbinary) {
                $thisRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
            }

            if ($serializerPhp) {
                $thisRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            }

            if ($prefix) {
                $thisRedis->setOption(\Redis::OPT_PREFIX, $prefix);
            }

            return $thisRedis;
        };
    }
}
