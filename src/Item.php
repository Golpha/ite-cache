<?php

namespace Ite\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Item
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class Item implements CacheItemInterface {

        /**
         *
         * @var string
         */
        protected $key;

        /**
         *
         * @var mixed
         */
        protected $value;

        /**
         *
         * @var int
         */
        protected $expire;

        public function __construct($key, $value = null, $expiration = null) {
                $this->key = $key;
                $this->value = $value;
                if ($expiration) {
                        $this->expiresAt($expiration);
                }
        }

        public function expiresAfter($time) {
                if ($time instanceof \DateInterval) {
                        $now = new \DateTime();
                        $now->add($time);
                        $this->expire = $now->getTimestamp();
                }
                else if ($time === null) {
                        $this->expire = null;
                }
                else {
                        $this->expire = time() + $time;
                }
                return $this;
        }

        public function expiresAt($expiration) {
                if ($expiration instanceof \DateTimeInterface) {
                        $this->expire = $expiration->getTimestamp();
                }
                else {
                        $this->expire = null;
                }
                return $this;
        }

        public function get() {
                return $this->value;
        }

        public function getKey() {
                return $this->key;
        }

        public function isHit() {
                return $this->expire !== null && time() < $this->expire;
        }

        public function set($value) {
                $this->value = $value;
                return $this;
        }

}
