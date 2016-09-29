<?php

chdir(realpath(__DIR__.'/..'));

require_once './vendor/autoload.php';

class FileCacheTests {

        protected $cache;

        public function __construct() {
                $this->cache = new Ite\Cache\FileContainer([], 5);
        }

        function get_time() {
                $this->cache->getItem('asd', time());
                $this->cache->commit();
                var_dump($this->cache->getItem('asd')->get());
        }

}

$test = new FileCacheTests;

$test->get_time();
sleep(4);
$test->get_time();
sleep(2);
$test->get_time();