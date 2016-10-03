<?php

namespace Ite\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Memcached Wrapper
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class Memcached extends AbstractPool {

        /**
         *
         * @var \Memcached
         */
        protected $memcached;

        /**
         *
         * @var array
         */
        protected $servers;

        /**
         *
         * @var string
         */
        protected $persistentId;

        /**
         *
         * @var array
         */
        protected $options;

        /**
         *
         * @param array $servers
         * @param string $persistentId
         * @param array $options
         * @param array $items
         * @param int|\DateInterval|null $expireTime
         * @throws \DomainException
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
         */
        public function initMemcached() {
                $this->memcached = new \Memcached($this->persistentId);
                if (count($this->options)) {
                        $this->memcached->setOptions($this->options);
                }
                if (!count($this->memcached->getServerList())) {
                        $this->memcached->addServers($this->servers);
                }
                $storedKeys = $this->memcached->getAllKeys();
                $this->clear();
                if ($storedKeys) {
                        foreach ($storedKeys as $key) {
                                $this->getItem($key);
                        }
                }
        }

        /**
         *
         * @return array
         */
        public function getServers() {
                return $this->servers;
        }

        /**
         *
         * @return string
         */
        public function getPersistentId() {
                return $this->persistentId;
        }

        /**
         *
         * @return array
         */
        public function getOptions() {
                return $this->options;
        }

        /**
         *
         * @param array $servers
         * @return \Ite\Cache\Memcached
         */
        public function setServers(array $servers) {
                $this->servers = $servers;
                return $this;
        }

        /**
         *
         * @param string $persistentId
         * @return \Ite\Cache\Memcached
         */
        public function setPersistentId($persistentId) {
                $this->persistentId = $persistentId;
                return $this;
        }

        /**
         *
         * @param array $options
         * @return bool
         */
        public function setOptions(array $options) {
                $this->options = $options;
                return $this->memcached->setOptions($options);
        }

        /**
         *
         * @param int $option
         * @param mixed $value
         * @return bool
         */
        public function setOption($option, $value) {
                $this->options[$option] = $value;
                return $this->memcached->setOption($option, $value);
        }

        /**
         *
         * @param string $host
         * @param int $port
         * @param int $weight [Optional] Default 0
         * @return bool
         */
        public function addServer($host, $port, $weight = 0) {
                $this->servers[] = [$host, $port, $weight];
                return $this->memcached->addserver($host, $port, $weight);
        }

        /**
         *
         * @return boolean
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
         *
         * @param string $key
         * @return CcacheItemInterface
         * @throws Exception\CacheException
         */
        public function getItem($key) {
                $this->verifyKey($key);
                $cached = $this->memcached->get($key);
                if (!$cached) {
                        if ($this->memcached->getResultCode() == \Memcached::RES_NOTFOUND) {
                                $cached = null;
                        }
                        else {
                                throw new Exception\CacheException($this->memcached->getLastErrorMessage());
                        }
                }
                $item = parent::getItem($key);
                if ($item->get() !== $cached) {
                        $item->set($cached);
                }
                return $item;
        }

        /**
         *
         * @param string $key
         * @return boolean
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
         *
         * @param CacheItemInterface $item
         * @return boolean
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
         * @param string $name
         * @param array $arguments
         * @return mixed
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
