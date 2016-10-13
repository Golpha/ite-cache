<?php

/**
 * CacheStrategyFactory file
 *
 * Copyright (c) 2016, Kiril Savchev
 * All rights reserved.
 *
 * @category Libs
 * @package Cache
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 *
 * @license http://www.apache.org/licenses/ Apache License 2.0
 * @link http://ifthenelse.info
 */
namespace Ite\Cache;

use Ite\Cache\Exception\CacheException;

/**
 * CacheStrategyFactory
 *
 * Creates a cache adapter based on provided parameters and registered stragies
 *
 * @version 1.0
 *
 * @author Kiril Savchev
 */
class CacheStrategyFactory {

        /**
         * Seconds in one hour
         */
        const HOUR = 3600;

        /**
         * Seconds in one day
         */
        const DAY = 86400;

        /**
         * Seconds in 30 days
         */
        const MONTH = 2592000;

        /**
         * Expire key for parameters
         */
        const EXPIRE_KEY = 'expire';

        /**
         * Added strategy' params
         *
         * @var array
         */
        protected static $cacheStrategies = [
                'file' => [
                        'class' => FileCache::class,
                        'params' => [
                                'cacheDir' => null,
                                'expire' => self::DAY
                        ]
                ],
                'session' => [
                        'class' => SessionCache::class,
                        'params' => [
                                'session_key' => 'ite_session_cache',
                                'expire' => self::HOUR
                        ]
                ],
                'memcached' => [
                        'class' => Memcached::class,
                        'params' => [
                                'persistentId' => 'ite_memcached_persistent',
                                'expire' => self::MONTH
                        ]
                ]

        ];

        /**
         * Registere strategy to internal container
         *
         * @param string $name Strategy name
         * @param array|string $params Adapter parameters
         * @throws Exception\InvalidArgumentException
         */
        public static function addStrategy($name, $params) {
                if (array_key_exists($name, static::$cacheStrategies)) {
                        throw new Exception\InvalidArgumentException("Strategy {$name} is already registered");
                }
                if (is_string($params)) {
                        static::$cacheStrategies[$name] = [ 'class' => $params];
                }
                else if (is_array($params)) {
                        if (!array_key_exists('class', $params)) {
                                throw new Exception\InvalidArgumentException("No strategy class defined");
                        }
                        static::$cacheStrategies[$name] = $params;
                }
                else {
                        throw new Exception\InvalidArgumentException("Invalid parameter name");
                }
        }

        /**
         * Checks whether a strategy is registered
         *
         * @param string $name Strategy name
         * @return bool True if exists, otherwise false
         */
        public static function hasStrategy($name) {
                return array_key_exists($name, static::$cacheStrategies);
        }

        /**
         * Unregister strategy
         *
         * @param string $name Strategy name
         */
        public static function removeStrategy($name) {
                if (static::hasStrategy($name)) {
                        unset(static::$cacheStrategies[$name]);
                }
        }

        /**
         * Dynamic alias of self::addStrategy()
         *
         * @param string $name Strategy name
         * @param array|string $params Strategy parameters
         */
        public function addCacheStrategy($name, $params) {
                static::addStrategy($name, $params);
        }

        /**
         * Dynamic alias of self::hasStrategy()
         *
         * @param string $name Strategy name
         * @return bool True if exists, otherwise false
         */
        public function hasCacheStrategy($name) {
                return static::hasStrategy($name);
        }

        /**
         * Dynamic alias of self::removeStrategy()
         *
         * @param string $name Strategy name
         */
        public function removeCacheStrategy($name) {
                static::removeStrategy($name);
        }

        /**
         * Creates a cache pool object
         *
         * It merges the default registered parameters with the second parameter
         *
         * @param string $name The registered cache strategy
         * @param array $params [Optional] Parameters for the pool
         * @return \Psr\Cache\CachePoolInterface
         *
         * @throws Exception\InvalidArgumentException If the wanted strategy is not registered or is with invalid parameters
         * @throws CacheException If the pool class is not a valid PSR-6 item pool class
         */
        public function create($name, array $params = []) {
                if (!array_key_exists($name, static::$cacheStrategies)) {
                        throw new Exception\InvalidArgumentException("Unknown cache strategy {$name}");
                }
                $strategyParams = static::$cacheStrategies[$name];
                if (!array_key_exists('class', $strategyParams)) {
                        throw new Exception\InvalidArgumentException("Undefined cache strategy class");
                }
                $reflection = new \ReflectionClass($strategyParams['class']);
                if (!$reflection->implementsInterface('Psr\Cache\CacheItemPoolInterface')) {
                        throw new CacheException("Invalid cache strategy");
                }
                if (array_key_exists('params', $strategyParams)) {
                        $defaultParams = $strategyParams['params'];
                }
                else {
                        $defaultParams = [];
                }
                $mergedParams = array_merge($defaultParams, $params);
                return $reflection->newInstanceArgs($mergedParams);
        }

}
