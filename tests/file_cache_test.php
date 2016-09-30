<?php

use Ite\Cache\FileCache;

chdir(realpath(__DIR__.'/..'));

require_once './vendor/autoload.php';

class FileCacheTests {

        /**
         *
         * @var FileCache
         */
        protected $cache;

        /**
         *
         * @var CacheItemInterface
         */
        protected $item;

        public function __construct($expireTime) {
                $this->cache = new FileCache();
                $this->cache->setExpireTime($expireTime);
        }

        function getTime() {
                if (!$this->item) {
                        $this->item = $this->cache->getItem('check_time');
                }
                if (!$this->item->isHit()) {
                        $this->item->set(time());
                        $this->cache->save($this->item);
                }
                return $this->item->get();
        }

}

//$expire = 3; // expires in 5 seconds
$expire = null; // no expire
$test = new FileCacheTests($expire);
$cached = $test->getTime();
echo "Cached value: {$cached}".PHP_EOL;
sleep(2);
echo "Call from cache: ".($cached === $test->getTime() ? 'true' : 'false').PHP_EOL;
sleep(2);
echo "Refresh cache: ".($cached !== $test->getTime() ? 'true' : 'false').PHP_EOL;