<?php

namespace Ite\Cache\Test;

use Ite\Cache\Item;

trait UnitTestTrait {

        /**
         *
         * @var string
         */
        protected $itemKey = 'testItem';

        /**
         *
         * @var string
         */
        protected $itemValue;

        /**
         *
         * @var \Ite\Cache\Item
         */
        protected $item;

        /**
         *
         * @var \Ite\Cache\AbstractPool
         */
        protected $cacheAdapter;

        public function __construct($name = null, array $data = [], $dataName = '') {
                parent::__construct($name, $data, $dataName);
                $this->itemValue = md5(time());
                $this->item = new Item($this->itemKey, $this->itemValue);
                if (method_exists($this, 'initCacheAdapter')) {
                        $this->initCacheAdapter();
                }
                $this->assertInstanceOf('Ite\Cache\CachePoolInterface', $this->cacheAdapter);
        }

        public function testSaveItem() {
                $this->cacheAdapter->save($this->item);
                $this->assertEquals(true, $this->cacheAdapter->hasItem($this->itemKey), 'Test having item');
        }

        public function testGetItem() {
                $this->cacheAdapter->save($this->item);
                $this->assertEquals($this->item, $this->cacheAdapter->getItem($this->itemKey));
        }

        public function testExpiration() {
                $expireIn = 2;
                $cachedValue = $this->item->get();
                $this->cacheAdapter->setExpireTime($expireIn);
                $this->cacheAdapter->save($this->item);
                sleep($expireIn+1);
                $this->assertNotEquals($cachedValue, $this->cacheAdapter->getItem($this->itemKey)->get());
        }

        public function testNoExpire() {
                $noExpire = null;
                $cachedValue = $this->item->get();
                $this->cacheAdapter->setExpireTime($noExpire);
                $this->cacheAdapter->save($this->item);
                $checks = rand(1, 5);
                for ($i=0; $i<$checks; $i++) {
                        $sleepTime = rand(1, 5);
                        sleep($sleepTime);
                        $this->assertEquals($cachedValue, $this->cacheAdapter->getItem($this->itemKey)->get());
                }
        }

        public function testDeleteItem() {
                $this->cacheAdapter->save($this->item);
                $this->cacheAdapter->deleteItem($this->itemKey);
                $this->assertNotSame($this->item, $this->cacheAdapter->getItem($this->itemKey));
        }

}