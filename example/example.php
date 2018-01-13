<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new SilexPhpRedis\PhpRedisProvider(), array(
    'redis.host' => '127.0.0.1',
    'redis.port' => 6379,
    'redis.timeout' => 30,
    'redis.persistent' => true,
    'redis.serializer.igbinary' => false, // use igBinary serialize/unserialize
    'redis.serializer.php' => false, // use built-in serialize/unserialize
    'redis.prefix' => 'myprefix',
    'redis.database' => '0'
));

/** routes **/
$app->get('/', function () use ($app) {
    return var_export($app['redis']->info(), true);
});

/** run application **/
$app->run();
