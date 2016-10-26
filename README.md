# ITE Cache
PSR-6 compliant ITE Cache library with different cache mechanisms. It natively supports
file, session and memcached adapters.

## File cache
Saves values into files and loads from them if requested and the cache is not
expired.

### Example
```php
<?php

use Ite\Cache\FileContainer;

chdir(realpath(__DIR__.'/..'));

require_once 'vendor/autoload.php';

class FileCacheTests {

    /**
     *
     * @var FileContainer
     */
    protected $cache;

    public function __construct($expireTime) 
    {
            $this->cache = new FileContainer();
            $this->cache->setExpireTime($expireTime);
    }

    function getTime() 
    {
            $item = $this->cache->getItem('check_time');
            
            if (!$item->isHit()) {
                    $item->set(time());
                    $this->cache->save($item);
            }
            
            return $item->get();
    }

}

$test = new FileCacheTests(5);
$cached = $test->getTime();
echo "Cached value: {$cached}".PHP_EOL;
sleep(4);
echo "Call from cache: ".($cached === $test->getTime() ? 'true' : 'false').PHP_EOL;
sleep(2);
echo "Refresh cache: ".($cached !== $test->getTime() ? 'true' : 'false').PHP_EOL;
```

## Session cache
Saves values into user session. It will be cleared after the user session expires

### Example

```php
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

        public function __construct($expireTime) 
        {
                $this->cache = new SessionContainer();
                $this->cache->setExpireTime($expireTime);
        }

        function getTime() 
        {
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
```

## Memcached adapter

```php
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

        public function __construct($expireTime) 
        {
                $this->cache = new Ite\Cache\Memcached(['localhost', 11211], 'testsMemcached');
                
                if ($expireTime) {
                        $this->cache->setExpireTime($expireTime);
                }
        }

        function getTime() 
        {
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
```
