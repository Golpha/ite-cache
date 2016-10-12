<?php

namespace Ite\Cache\Test;

use Ite\Cache\FileCache;
use PHPUnit\Framework\TestCase;

/**
 * FileCacheTest
 *
 * @author vis
 */
class FileCacheTest extends TestCase {

        use UnitTestTrait;

        protected $cacheAdapterClass = 'Ite\Cache\FileCache';

        public function initCacheAdapter() {
                $this->cacheAdapter = new FileCache();
        }
}
