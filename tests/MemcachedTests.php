<?php

namespace Ite\Cache\Test;

use Ite\Cache\Memcached;
use PHPUnit\Framework\TestCase;

/**
 * Description of MemcachedTests
 *
 * @author vis
 */
class MemcachedTests extends TestCase {

        use UnitTestTrait;

        public function initCacheAdapter() {
                $this->cacheAdapter = new Memcached();
        }

}
