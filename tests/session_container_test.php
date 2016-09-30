<?php

use Ite\Cache\SessionContainer;
use Psr\Cache\CacheItemInterface;

chdir(realpath(__DIR__.'/..'));

require_once './vendor/autoload.php';

class SessionCacheTests {

        /**
         *
         * @var SessionContainer
         */
        protected $cache;

        /**
         *
         * @var CacheItemInterface
         */
        protected $item;

        public function __construct($expireTime) {
                $this->cache = new SessionContainer();
                $this->cache->setExpireTime($expireTime);
        }

        function getTime() {
                $this->item = $this->cache->getItem('check_time');
                if (!$this->item->isHit()) {
                        $this->item->set(time());
                        $this->cache->save($this->item);
                }
                return $this->item->get();
        }

}

$expire = 3; // expires in 5 seconds
//$expire = null; // no expire
$test = new SessionCacheTests($expire);
$cached = $test->getTime();
echo "Cached value: {$cached}<br />";
sleep(2);
echo "Call from cache: ".($cached === $test->getTime() ? 'true' : 'false').'<br />';
sleep(2);
echo "Refresh cache: ".($cached !== $test->getTime() ? 'true' : 'false').'<br />';