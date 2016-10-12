<?php

namespace Ite\Cache\Test;

use Ite\Cache\SessionCache;
use PHPUnit\Framework\TestCase;

/**
 * Description of SessionCacheTest
 *
 * @author vis
 */
class SessionCacheTest extends TestCase {

        use UnitTestTrait;

        public function initCacheAdapter() {
                $this->cacheAdapter = new SessionCache();
        }

}
