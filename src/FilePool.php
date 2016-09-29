<?php

namespace Ite\Cache;

/**
 * FilePool
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class FilePool extends AbstractPool {


        /**
         *
         * @var string
         */
        protected $cacheDir;

        /**
         *
         * @param array $items
         * @param string $cacheDir
         * @throws Exception\CacheException
         */
        public function __construct(array $items = [], $cacheDir = '') {
                if (!$cacheDir) {
                        $this->cacheDir = realpath(__DIR__.'/../cache');
                }
                else {
                        $this->cacheDir = $cacheDir;
                }
                if (!file_exists($this->cacheDir)) {
                        throw new Exception\CacheException("Cache directory does not exists");
                }
                if (!is_dir($this->cacheDir)) {
                        throw new Exception\CacheException("Cache directory is not a directory");
                }
                if (!is_writable($this->cacheDir)) {
                        throw new Exception\CacheException("Cache directory is not writable");
                }
                parent::__construct($items);
        }

}
