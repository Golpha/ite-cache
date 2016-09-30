<?php

namespace Ite\Cache;

use Ite\Cache\Exception\CacheException;
use Psr\Cache\CacheItemInterface;

/**
 * FilePool
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class FileContainer extends AbstractPool {


        /**
         *
         * @var string
         */
        protected $cacheDir;

        /**
         *
         * @param string $cacheDir
         * @param int $expireTime
         * @param array $items
         * @throws CacheException
         */
        public function __construct($cacheDir = '', $expireTime = null, array $items = []) {
                if (!$cacheDir) {
                        $this->cacheDir = realpath(__DIR__.'/../cache');
                }
                else {
                        $this->cacheDir = $cacheDir;
                }
                if (!file_exists($this->cacheDir)) {
                        throw new CacheException("Cache directory does not exists");
                }
                if (!is_dir($this->cacheDir)) {
                        throw new CacheException("Cache directory is not a directory");
                }
                if (!is_writable($this->cacheDir)) {
                        throw new CacheException("Cache directory is not writable");
                }
                parent::__construct($items, $expireTime);
        }

        /**
         *
         * @param CacheItemInterface $item
         * @return boolean
         */
        public function save(CacheItemInterface $item) {
                if (file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.$item->getKey(), serialize($item->get()))) {
                        return parent::save($item);
                }
                else {
                        return false;
                }
        }

        /**
         *
         * @param string $key
         * @return bool
         */
        public function deleteItem($key) {
                $path = $this->cacheDir.DIRECTORY_SEPARATOR.$key;
                if (file_exists($path)) {
                        unlink($path);
                }
                return parent::deleteItem($key);
        }

        /**
         *
         * @param string $key
         * @return \Ite\Cache\Item
         */
        public function getItem($key) {
                $item = parent::getItem($key);
                $file = $this->cacheDir.DIRECTORY_SEPARATOR.$key;
                if (file_exists($file)) {
                        if ($this->expireTime > 0 && filectime($file) + $this->expireTime < time()) {
                                $this->deleteItem($key);
                                $item = new Item($key);
                                $this->saveDeferred($item);
                        }
                        else {
                                $item->set(unserialize(file_get_contents($file)));
                        }
                }
                return $item;
        }

}
