<?php

namespace SilexPredis;

use Silex\Application;
use Silex\ServiceProviderInterface;

class PhpRedisProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {

    }

    public function register(Application $app)
    {
        $app['redis'] = $app->share(function () use ($app) {
            $thisRedis = new \Redis();
            $host = isset($app['redis.host']) ? $app['redis.host'] : array();
            $port = isset($app['redis.port'])  && is_int($app['redis.port']) ? $app['redis.port'] : 6379;
            $timeout = isset($app['redis.timeout']) && is_int($app['redis.timeout']) ? $app['redis.timeout'] : 0;
            $persistent = isset($app['redis.persistent']) ? $app['redis.persistent'] : false;
            $auth = isset($app['redis.auth']) ? $app['redis.auth'] : null;

            if ($persistent){
                $thisRedis->pconnect($host,$port,$timeout);
            } else {
                $thisRedis->connect($host,$port,$timeout);
            }

            if(!empty($auth)){
                $thisRedis->auth($auth);
            }

            return $thisRedis;
        });
    }
}
