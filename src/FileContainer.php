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
                if (file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.$item->getKey(), serialize($item->get()))) {
                        return parent::save($item);
                }
                else {
                        return false;
                }
        }

        public function delete(CacheItemInterface $item) {
                $path = $this->cacheDir.DIRECTORY_SEPARATOR.$item->getKey();
                return (!file_exists($path) || unlink($path));
        }

        public function getItem($key, $value = '') {
                $item = parent::getItem($key);
                if (!($item instanceof Item)) {
                        var_dump("Line: ".__LINE__);
                        $file = $this->cacheDir.DIRECTORY_SEPARATOR.$key;
                        if (file_exists($file)) {
                                if ($this->expireTime !== null && filectime($file) + $this->expireTime < time()) {
                                        unlink($file);
                                        $item = new Item($key, $value);
                                        $this->saveDeferred($item);
                                }
                                else {
                                        $item = new Item($key, unserialize(file_get_contents($file)));
                                        $item->expiresAfter(($this->expireTime) ? new \DateInterval('PT'.$this->expireTime.'S') : null);
                                        $this->items[$key] = $item;
                                }
                        }
                        else {
                                $item = new Item($key, $value);
                                $this->saveDeferred($item);
                        }
                }
                else if (!$item->isHit()) {
                        var_dump("Line: ".__LINE__);
                        $this->delete($item);
                        $item = new Item($key, $value);
                        $this->saveDeferred($item);
                }
                return $item;
        }

}
