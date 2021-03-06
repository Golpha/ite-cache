<?php
/**
 * AbstractPool class file
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

use Psr\Cache\CacheItemInterface;

/**
 * AbstractPool
 *
 * The base class for all package's concrete cache adapters
 *
 * @version 1.0
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
abstract class AbstractPool implements CachePoolInterface {

        /**
         * Saved cached items
         *
         * @var array
         */
        protected $items;

        /**
         * Deffered items to be cached
         *
         * @var array
         */
        protected $deffered;

        /**
         * Item's lifetime
         *
         * @var int
         */
        protected $expireTime;

        /**
         * Creates a cache adapter with some items and expire time
         *
         * @param array $items [Optional] The items to be stored
         * @param int|\DateInterval|null $expireTime [Optional] The expire time
         */
        public function __construct(array $items = [], $expireTime = null) {
                $this->items = [];
                $this->deffered = [];
                if ($items) {
                        $this->items = $items;
                }
                $this->setExpireTime($expireTime);
        }

        /**
         * Verify the searched item key
         *
         * It may consist of only alphanumerical values, dots and underscores,
         * with length range between 1 and 64.
         * If a key is invalid it will throw an exceptions
         *
         * @param string $key The item key
         * @throws Exception\InvalidArgumentException
         */
        protected function verifyKey($key) {
                if (!preg_match('/^[a-z0-9\.\_]{1,64}$/i', $key)) {
                        throw new Exception\InvalidArgumentException("Invalid key {$key}");
                }
        }

        /**
         * Clear the items in the pool
         *
         * The method of this abstract class clears the items only from the
         * class containers of stored and differed items but not from the actual
         * caching system. The ancestors must implement the actual cache cleaning.
         *
         * @return bool True when deffered and stored item container are empty
         */
        public function clear() {
                $this->items = [];
                $this->deffered = [];
                return empty($this->items) && empty($this->deffered);
        }

        /**
         * Saves the deffered cache items
         *
         * If for an item could not be saved this method will return false,
         * althogh it will continue trying to save the remain part of the set.
         *
         * @return bool True if all items are stored and false if one fails
         */
        public function commit() {
                $result = true;
                foreach ($this->deffered as $item) {
                        $result = $this->save($item) && $result;
                        if ($result) {
                                unset($this->deffered[$item->getKey()]);
                        }
                }
                return $result;
        }

        /**
         * Delete an item by key
         *
         * It deletes only from the class containers but not from the actual
         * caching system. The class ancestors must implement the actual deleting
         *
         * @param string $key The item key
         * @return bool True on success, false on fail
         * @throws Exception\InvalidArgumentException
         */
        public function deleteItem($key) {
                $this->verifyKey($key);
                if (array_key_exists($key, $this->items)) {
                        unset($this->items[$key]);
                }
                else if (array_key_exists($key, $this->deffered)) {
                        unset($this->deffered[$key]);
                }
                return !$this->hasItem($key);
        }

        /**
         * Delete items by a set of keys
         *
         * The set will be traversed and the deleteItem() will be invoked with
         * every value, so implementing an actual single item deleting will be
         * applyed among this method too.
         * If an item couldn't be removed for some reasong the method will return
         * false although it will continue to try removing the remain part of the se
         *
         * @param array $keys A set with item keys. If an item with a key not exists, it will be skipped
         * @return bool True if all the items are removed and false if one or more couldn't
         * @throws Exception\InvalidArgumentException
         */
        public function deleteItems(array $keys) {
                $result = true;
                foreach ($keys as $key) {
                        $result = $this->deleteItem($key) && $result;
                }
                return $result;
        }

        /**
         * Retrieve a cached item by key
         *
         * If no item is stored in the saved or deffered containers a new one
         * will be created with no expire time and null for value.
         * This method actualy does not search in the caching system but in the
         * inner class containers and its ancestor may reimplement this behaviour.
         *
         * @param string $key The item key
         * @return CacheItemInterface The found or newly created item
         * @throws Exception\InvalidArgumentException
         */
        public function getItem($key) {
                $this->verifyKey($key);
                if (array_key_exists($key, $this->items)) {
                        $item = $this->items[$key];
                }
                else if (array_key_exists($key, $this->deffered)) {
                        $item = $this->deffered[$key];
                }
                else {
                        $item = new Item($key);
                        $this->deffered[$key] = $item;
                }
                return $item;
        }

        /**
         * Get a set of items
         *
         * If no set of keys is provided this method will return all the items in
         * the saved and deffered containers. If a set is provided it will be
         * traversed with the getItem() method and the result will be returned.
         *
         * @param array $keys [Optional] A set of keys
         * @return array The set of found items
         * @throws Exception\InvalidArgumentException
         */
        public function getItems(array $keys = []) {
                if (!$keys) {
                        return array_merge($this->items, $this->deffered);
                }
                $items = [];
                foreach ($keys as $key) {
                        $items[$key] = $this->getItem($key);
                }
                return $items;
        }

        /**
         * Checks whether an item with key exists
         *
         * It will checkes both of stored and deffered containers
         *
         * @param string $key The item key
         * @return bool True if exists, otherwise false
         * @throws Exception\InvalidArgumentException
         */
        public function hasItem($key) {
                $this->verifyKey($key);
                return array_key_exists($key, $this->items) || array_key_exists($key, $this->deffered);
        }

        /**
         * Saves an item
         *
         * Adds an item to the saved container, adding the expire time to it.
         * This method does not actual save the item to the caching system and
         * the class ancesstors must implement it. The concrete cache adapters
         * may not caches the item object itself but may use only its value.
         *
         * @param CacheItemInterface $item The item to be stored
         * @return bool True on success, otherwise false
         */
        public function save(CacheItemInterface $item) {
                $item->expiresAfter($this->expireTime);
                $this->items[$item->getKey()] = $item;
                return $this->hasItem($item->getKey());
        }

        /**
         * Defferes an item for future caching save
         *
         * @param CacheItemInterface $item The cache item
         * @return bool True on success, otherwise false
         */
        public function saveDeferred(CacheItemInterface $item) {
                $this->deffered[$item->getKey()] = $item;
                return $this->hasItem($item->getKey());
        }

        /**
         * Sets an expire time for the items
         *
         * @param null|int|\DateInterval $expireTime The expire time
         * @return \Ite\Cache\AbstractPool This object
         */
        public function setExpireTime($expireTime) {
                if ($expireTime instanceof \DateInterval) {
                        $this->expireTime = $expireTime->s;
                }
                else if (is_int($expireTime) || is_null($expireTime)) {
                        $this->expireTime = $expireTime;
                }
                return $this;
        }
        /**
         * Short method of saving item's value
         *
         * It will use the save() method so the logic of saving to the cache
         * system, implemented there, will be triggered and does not need to
         * extend this method's functionality
         *
         * @param string $key The item key
         * @param mixed $value The item value
         * @return \Ite\Cache\AbstractPool This object
         */
        public function set($key, $value) {
                $item = $this->getItem($key);
                $item->set($value);
                $this->save($item);
                return $this;
        }

}
