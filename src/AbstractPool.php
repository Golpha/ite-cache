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

        public function __construct(array $items = []) {
                if ($items) {
                        $this->items = $items;
                }
        }

        protected function verifyKey($key) {
                if (!preg_match('/^[a-z0-9\.\_]{1,64}$/i', $key)) {
                        throw new Exception\InvalidArgumentException("Invalid key {$key}");
                }
        }

        public function clear() {
                $this->items = [];
                $this->deffered = [];
                return count($this->items) == 0 && count($this->deffered) == 0;
        }

        public function commit() {
                $result = true;
                foreach ($this->deffered as $item) {
                        $result = $this->save($item) && $result;
                }
                return $result;
        }

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

        public function deleteItems(array $keys) {
                $result = true;
                foreach ($keys as $key) {
                        $result = $this->deleteItem($key) && $result;
                }
                return $result;
        }

        public function getItem($key) {
                $this->verifyKey($key);
                if (array_key_exists($key, $this->items)) {
                        return $this->items[$key];
                }
                else if (array_key_exists($key, $this->deffered)) {
                        return $this->deffered[$key];
                }
                else {
                        $this->deffered[$key] = new Item($key);
                        return $this->deffered[$key];
                }
        }

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

        public function hasItem($key) {
                $this->verifyKey($key);
                return array_key_exists($key, $this->items) || array_key_exists($key, $this->deffered);
        }

        public function save(CacheItemInterface $item) {
                $this->items[$item->getKey()] = $item;
                return $this->hasItem($key);
        }

        public function saveDeferred(CacheItemInterface $item) {
                $this->deffered[$item->getKey()] = $item;
                return $this->hasItem($key);
        }

}
