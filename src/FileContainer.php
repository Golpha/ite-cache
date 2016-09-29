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
         * @param array $items
         * @param string $cacheDir
         * @throws CacheException
         */
        public function __construct(array $items = [], $expireTime = null, $cacheDir = '') {
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
                if (file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.$item->getKey(), $item->get())) {
                        return parent::save($item);
                }
                else {
                        return false;
                }
        }

        /**
         *
         * @param string $key
         * @return boolean
         */
        public function deleteItem($key) {
                $item = $this->getItem($key);
                $path = $this->cacheDir.DIRECTORY_SEPARATOR.$item->getKey();
                if (!file_exists($path) || unlink($path)) {
                        return parent::deleteItem($key);
                }
                else {
                        return false;
                }
        }

        public function getItem($key) {
                $item = parent::getItem($key);
                if (!($item instanceof Item)) {
                        $file = $this->cacheDir.DIRECTORY_SEPARATOR.$key;
                        if (file_exists($file)) {
                                $item = new Item($key, file_get_contents($file));
                                $item->expiresAfter(($this->expireTime) ? new \DateInterval('P'.$this->expireTime.'S') : null);
                                $this->items[$key] = $item;
                        }
                        else {
                                $item = new Item($key);
                                $this->saveDeferred($item);
                        }
                }
                else if (!$item->isHit()) {
                        $this->deleteItem($item->getKey());
                        $item = new Item($key);
                        $this->saveDeferred($item);
                }
                return $item;
        }

}
