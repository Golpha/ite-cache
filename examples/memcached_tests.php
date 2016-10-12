<?php

chdir(realpath(__DIR__.'/..'));

require_once './vendor/autoload.php';

class MemcachedTests {

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
                $this->cache = new Ite\Cache\Memcached(['localhost', 11211], 'testsMemcached');
                if ($expireTime) {
                        $this->cache->setExpireTime($expireTime);
                }
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

$expire = 3; // expires in 3 seconds
//$expire = 0; // no expire
$test = new MemcachedTests($expire);
$cached = $test->getTime();
echo "Cached value: {$cached}".PHP_EOL;
sleep(2);
echo "Call from cache: ".($cached === $test->getTime() ? 'true' : 'false').PHP_EOL;
sleep(2);
echo "Refresh cache: ".($cached !== $test->getTime() ? 'true' : 'false').PHP_EOL;