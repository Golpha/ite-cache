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
        protected $expiration;

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
                        $this->expiration = $now->getTimestamp();
                }
                else {
                        $this->expiration = null;
                }
                return $this;
        }

        public function expiresAt($expiration) {
                if ($expiration instanceof \DateTimeInterface) {
                        $this->expiration = $expiration->getTimestamp();
                }
                else {
                        $this->expiration = null;
                }
                return $this;
        }

        public function get() {
                if (!$this->isHit()) {
                        return null;
                }
                return $this->value;
        }

        public function getKey() {
                return $this->key;
        }

        public function isHit() {
                return $this->expiration === null || time() > $this->expiration;
        }

        public function set($value) {
                $this->value = $value;
                return $this;
        }

}
