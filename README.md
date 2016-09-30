# ITE Cache
PSR6 ITE Cache library with different cache mechanisms

## File cache
Saves values into files and loads from them if requested and the cache is not
expired.

### Example
    <?php

    use Ite\Cache\FileContainer;

    chdir(realpath(__DIR__.'/..'));

    require_once './vendor/autoload.php';

    class FileCacheTests {

            /**
             *
             * @var FileContainer
             */
            protected $cache;

            public function __construct($expireTime) {
                    $this->cache = new FileContainer();
                    $this->cache->setExpireTime($expireTime);
            }

            function getTime() {
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