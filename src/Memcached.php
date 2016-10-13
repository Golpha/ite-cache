<?php

/**
 * Memcached file
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
 * Memcached Wrapper
 *
 * This cache adapter wraps a memcached object to be compliant with
 * PSR6 Cache
 *
 * @version 1.0
 *
 * @uses \Memcached The native PHP memcached extension
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class Memcached extends AbstractPool {

        /**
         * The wrapped memcached object
         *
         * @var \Memcached
         */
        protected $memcached;

        /**
         * Servers list
         *
         * @var array
         */
        protected $servers;

        /**
         * The persistent id
         *
         * @var string
         */
        protected $persistentId;

        /**
         * Options list
         *
         * @var array
         */
        protected $options;

        /**
         * Creates new ITE-Cache Memcached object
         *
         * @param array $servers Server parameters or array with servers
         * @param string $persistentId [Optional] The persistent id. Default is null
         * @param array $options [Optional] Array with options. Default empty array
         * @param array $items [Optional] List with cached items. Default empty array
         * @param int|\DateInterval|null $expireTime [Optional] Time to expire. Default is null
         * @throws \DomainException If memcached extension is not loaded
         */
        public function __construct(array $servers, $persistentId = null, array $options = [], array $items = [], $expireTime = null) {
                if (!class_exists('\Memcached')) {
                        throw new \DomainException("Memcached extension is not active");
                }
                parent::__construct($items, $expireTime);
                if (array_key_exists(0, $servers)) {
                        if (is_array($servers[0])) {
                                $this->servers = $servers;
                        }
                        else {
                                $this->servers = [$servers];
                        }
                }
                $this->persistentId = $persistentId;
                $this->options = $options;
                $this->initMemcached();
        }

        /**
         * Initialize memcached object
         *
         * Instantiates a new memcached object and adds the options and the
         * servers list to it.
         */
        public function initMemcached() {
                $this->memcached = new \Memcached($this->persistentId);
                if (count($this->options)) {
                        $this->memcached->setOptions($this->options);
                }
                if (!count($this->memcached->getServerList())) {
                        $this->memcached->addServers($this->servers);
                }
        }

        /**
         * Returns the servers list
         *
         * @return array The servers list
         */
        public function getServers() {
                return $this->servers;
        }

        /**
         * Returns the persitent id
         *
         * @return string The persistent id
         */
        public function getPersistentId() {
                return $this->persistentId;
        }

        /**
         * Returns the memcached options
         *
         * @return array The options list
         */
        public function getOptions() {
                return $this->options;
        }

        /**
         * Sets the servers list
         *
         * @param array $servers The servers list
         * @return \Ite\Cache\Memcached This object
         */
        public function setServers(array $servers) {
                $this->servers = $servers;
                return $this;
        }

        /**
         * Sets the persistent id
         *
         * @param string $persistentId The persistent id for memcached
         * @return \Ite\Cache\Memcached This object
         */
        public function setPersistentId($persistentId) {
                $this->persistentId = $persistentId;
                return $this;
        }

        /**
         * Sets multiple memcached options
         *
         * @param array $options A list with options
         * @return bool The result of adding the options
         */
        public function setOptions(array $options) {
                $this->options = $options;
                return $this->memcached->setOptions($options);
        }

        /**
         * Sets memcached option
         *
         * @param int $option The option name. Tipicaly the \Memcached::OPT_* constant
         * @param mixed $value The option's value
         * @return bool The result of setting the option
         */
        public function setOption($option, $value) {
                $this->options[$option] = $value;
                return $this->memcached->setOption($option, $value);
        }

        /**
         * Returns the wrapped memcached instance
         *
         * @return \Memcached The wrapped instance
         */
        public function getMemcached() {
                return $this->memcached;
        }

        /**
         * Sets wrapped memcached instance
         *
         * When setting the wrapped instance, this method will retrieve also the
         * servers list and its options values.
         *
         * @param \Memcached $memcached The instance to be wrapped
         * @return \Ite\Cache\Memcached This object
         */
        public function setMemcached(\Memcached $memcached) {
                $this->memcached = $memcached;
                $this->servers = $this->memcached->getServerList();
                $reflection = new \ReflectionClass(get_class($memcached));
                $constants = array_filter($reflection->getConstants(), function($key) {
                        return substr($key, 0, 4) == 'OPT_';
                }, ARRAY_FILTER_USE_KEY);
                $this->options = [];
                foreach ($constants as $value) {
                        $this->options[$value] = $memcached->getOption($value);
                }
                return $this;
        }

        /**
         * Add a server to the memcached instance
         *
         * @param string $host The server's host
         * @param int $port The server's port
         * @param int $weight [Optional] The server's weight. Default 0
         * @return bool The result of adding the server
         */
        public function addServer($host, $port, $weight = 0) {
                $this->servers[] = [$host, $port, $weight];
                return $this->memcached->addserver($host, $port, $weight);
        }

        /**
         * Resets the server list
         *
         * @return boolean True on success, otherwise false
         */
        public function resetServerList() {
                if ($this->memcached->resetServerList()) {
                        $this->servers = [];
                        return true;
                }
                else {
                        return false;
                }
        }

        /**
         * Gets a cached item by key
         *
         * If there is no item with such key it will be created as a deffered
         * into the item's pool with no expire time and null for value. It is
         * good to check the isHit() method of the returned value to determine
         * whether the item is get from the pull or is newly created one.
         *
         * @param string $key The item's key
         * @return CcacheItemInterface The cached or newly created item
         * @throws Exception\CacheException
         */
        public function getItem($key) {
                $this->verifyKey($key);
                // get the cached value from the memcached servers:
                $cached = $this->memcached->get($key);
                // checks the cached value:
                if (!$cached) {
                        // if not found sets null
                        if ($this->memcached->getResultCode() == \Memcached::RES_NOTFOUND) {
                                $cached = null;
                        }
                        // otherwise throw an exception with the error message:
                        else {
                                throw new Exception\CacheException($this->memcached->getLastErrorMessage());
                        }
                }
                // creates the cached item
                $item = parent::getItem($key);
                // if is not hit (the item is not from cache) then add the retrieved value and expiration date
                if (!$item->isHit()) {
                        $item->set($cached);
                        $item->expiresAfter($this->expireTime);
                }
                return $item;
        }

        /**
         * Delete a cached item
         *
         * @param string $key The key of the item that will be deleted
         * @return boolean The result of item deleting
         */
        public function deleteItem($key) {
                if (parent::deleteItem($key)) {
                        return $this->memcached->delete($key);
                }
                else {
                        return false;
                }
        }

        /**
         * Saves an item to the cache engine
         *
         * @param CacheItemInterface $item The item to be stored
         * @return boolean The result of saving the item
         */
        public function save(CacheItemInterface $item) {
                if (parent::save($item)) {
                        return $this->memcached->set($item->getKey(), $item->get(), (int) $this->expireTime);
                }
                else {
                        return false;
                }
        }

        /**
         * Delegates methods to \Memcached
         *
         * @param string $name The method name
         * @param array $arguments The arguments
         * @return mixed The result of the memcached's method
         * @throws \BadMethodCallException
         */
        public function __call($name, $arguments) {
                if (method_exists($this->memcached, $name)) {
                        return call_user_func_array([$this->memcached, $name], $arguments);
                }
                else {
                        throw new \BadMethodCallException("Undefined method {$name}");
                }
        }

}
