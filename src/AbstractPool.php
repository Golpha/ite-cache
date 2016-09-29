<?php

namespace Ite\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * AbstractPool
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
abstract class AbstractPool implements CacheItemPoolInterface {

        /**
         *
         * @var array
         */
        protected $items;

        /**
         *
         * @var array
         */
        protected $deffered;

        /**
         *
         * @var int
         */
        protected $expireTime;

        /**
         *
         * @param array $items
         */
        public function __construct(array $items = [], $expireTime = null) {
                $this->items = [];
                $this->deffered = [];
                if ($items) {
                        $this->items = $items;
                }
                $this->expireTime = $expireTime;
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
                $commited = [];
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
                        return $this->items[$key];
                }
                else if (array_key_exists($key, $this->deffered)) {
                        return $this->deffered[$key];
                }
                else {
                        return null;
                }
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
                if ($item->isHit()) {
                        $item->expiresAfter(($this->expireTime) ? new \DateInterval('PT'.$this->expireTime.'S') : null);
                        $this->items[$item->getKey()] = $item;
                }
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

}
