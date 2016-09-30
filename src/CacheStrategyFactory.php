<?php

namespace Ite\Cache;

/**
 * CacheStrategyFactory
 *
 * @author Kiril Savchev
 */
class CacheStrategyFactory {

        const HOUR = 3600;

        const DAY = 86400;

        const EXPIRE_KEY = 'expire';

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
                                'session_key' => 'session_cache',
                                'expire' => self::HOUR
                        ]
                ]

        ];

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
                        throw new Exception\CacheException("Invalid cache strategy");
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
