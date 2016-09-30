<?php

namespace Ite\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * AbstractPool
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
         *
         * @param array $items
         * @param int|\DateInterval|null $expireTime
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
         *
         * @param string $key
         * @throws Exception\InvalidArgumentException
         */
        protected function verifyKey($key) {
                if (!preg_match('/^[a-z0-9\.\_]{1,64}$/i', $key)) {
                        throw new Exception\InvalidArgumentException("Invalid key {$key}");
                }
        }

        /**
         *
         * @return bool
         */
        public function clear() {
                $this->items = [];
                $this->deffered = [];
                return !count($this->items) && !count($this->deffered);
        }

        /**
         *
         * @return bool
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
         *
         * @param string $key
         * @return bool
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
         *
         * @param array $keys
         * @return bool
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
         *
         * @param string $key
         * @return CacheItemInterface
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
         *
         * @param array $keys
         * @return bool
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
         *
         * @param string $key
         * @return bool
         * @throws Exception\InvalidArgumentException
         */
        public function hasItem($key) {
                $this->verifyKey($key);
                return array_key_exists($key, $this->items) || array_key_exists($key, $this->deffered);
        }

        /**
         *
         * @param CacheItemInterface $item
         * @return bool
         */
        public function save(CacheItemInterface $item) {
                $item->expiresAfter($this->expireTime);
                $this->items[$item->getKey()] = $item;
                return $this->hasItem($item->getKey());
        }

        /**
         *
         * @param CacheItemInterface $item
         * @return bool
         */
        public function saveDeferred(CacheItemInterface $item) {
                $this->deffered[$item->getKey()] = $item;
                return $this->hasItem($item->getKey());
        }

        /**
         *
         * @param null|int|\DateInterval $expireTime
         * @return \Ite\Cache\AbstractPool
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
         * Short method of creating item and saving it
         *
         * @param string $key
         * @param mixed $value
         * @return \Ite\Cache\AbstractPool
         */
        public function set($key, $value) {
                $this->save(new Item($key, $value));
                return $this;
        }

}
