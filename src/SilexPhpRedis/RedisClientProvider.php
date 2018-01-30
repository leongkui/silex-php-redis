<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace SilexPhpRedis;

use Silex\Application;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * Description of RedisClientProvider
 *
 * @author igor.scabini
 */
class RedisClientProvider implements ServiceProviderInterface
{
    protected $prefix;

    /**
     * @param string $prefix Prefix name used to register the service provider in Silex.
     */
    public function __construct($prefix = 'redis')
    {
        if (empty($prefix)) {
            throw new InvalidArgumentException('The specified prefix is not valid.');
        }
        $this->prefix = $prefix;
    }

    /**
     * Returns an anonymous function used by the service provider initialize
     * lazily new instances of Redis\Client.
     *
     * @param Application $app
     * @param string      $prefix
     *
     * @return \Closure
     */
    protected function getClientInitializer(Application $app, $prefix)
    {
        return $app->protect(function ($args) use ($app, $prefix) {
            $extract = function ($bag, $key) use ($app, $prefix) {
                $default = "default_$key";
                if ($bag instanceof Application) {
                    $key = "$prefix.$key";
                    var_dump($key);
                }
                if (!isset($bag[$key])) {
                    return $app["$prefix.$default"];
                }
                if (is_array($bag[$key])) {
                    return array_merge($app["$prefix.$default"], $bag[$key]);
                }
                return $bag[$key];
            };
            if (isset($args['parameters']) && is_string($args['parameters'])) {
                $args['parameters'] = $app["$prefix.uri_parser"]($args['parameters']);
            }
            $parameters = $extract($args, 'parameters');
            $options = $extract($args, 'options');

            return $app["$prefix.client_constructor"]($parameters, $options);
        });
    }

    /**
     * Returns an anonymous function used by the service provider to handle
     * accesses to the root prefix.
     *
     * @param Application $app
     * @param string      $prefix
     *
     * @return mixed
     */
    protected function getProviderHandler(Application $app, $prefix)
    {
        return function () use ($app, $prefix) {
            $initializer = $app["$prefix.client_initializer"];
            return $initializer($app);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $prefix = $this->prefix;

        $app["$prefix.default_parameters"] = array();
        $app["$prefix.default_options"] = array();
        $app["$prefix.uri_parser"] = $app->protect(function ($uri) {
            return Parameters::parse($uri);
        });

        $app["$prefix.client_constructor"] = $app->protect(function ($parameters, $options) {
            $thisRedis = new \Redis();

            $host = isset($parameters['host']) ? $parameters['host'] : array();
            $port = isset($parameters['port']) && is_int($parameters['port']) ? $parameters['port'] : 6379;
            $timeout = isset($parameters['timeout']) && is_int($parameters['timeout']) ? $parameters['timeout'] : 0;
            $persistent = isset($parameters['persistent']) ? $parameters['persistent'] : false;
            $auth = isset($parameters['auth']) ? $parameters['auth'] : null;
            $serializerIgbinary = isset($options['serializer.igbinary']) ? $options['serializer.igbinary'] : false;
            $serializerPhp = isset($options['serializer.php']) ? $options['serializer.php'] : false;
            $prefix = isset($options['prefix']) ? $options['prefix'] : null;
            $database = isset($parameters['database']) ? $parameters['database'] : null;

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
        });

        $app["$prefix.client_initializer"] = $this->getClientInitializer($app, $prefix);
        $app["$prefix"] = $this->getProviderHandler($app, $prefix);
    }
}
