<?php

/**
 * CachePoolInterface file
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

use Psr\Cache\CacheItemPoolInterface;

/**
 * CachePoolInterface
 *
 * Extended Psr\Cache\CacheItemPoolInterface with some extra methods
 *
 * @version 1.0
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
interface CachePoolInterface extends CacheItemPoolInterface {

        /**
         * Sets the expire time of the item
         *
         * It may be a null for no expire, integer for a number of seconds that
         * the item will be valid and a DateInterval object in witch the item
         * will be valid.
         *
         * @param null|int|\DateInterval $expireTime
         */
        public function setExpireTime($expireTime);

        /**
         * Sets a value to item
         *
         * It sets the given $value to an item registered under $key. If no item
         * presents with such key it will be created.
         *
         * @param string $key
         * @param mixed $value
         */
        public function set($key, $value);

}
