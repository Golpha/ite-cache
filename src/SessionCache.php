<?php

namespace Ite\Cache;

use Ite\Cache\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

/**
 * SessionCache
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class SessionCache extends AbstractPool {

        protected $sessionKey;

        public function __construct($sessionKey = 'session_cache', $expireTime = null) {
                if ($sessionKey) {
                        $this->sessionKey = $sessionKey;
                }
                else {
                        throw new InvalidArgumentException("No session key provided");
                }
                if (!session_id()) {
                        session_start();
                }
                if (!array_key_exists($this->sessionKey, $_SESSION) || !is_array($_SESSION[$this->sessionKey])) {
                        $_SESSION[$this->sessionKey] = [];
                }
                parent::__construct([], $expireTime);
        }

        public function save(CacheItemInterface $item) {
                $saved = parent::save($item);
                if ($saved) {
                        $_SESSION[$this->sessionKey][$item->getKey()] = ['item' => serialize($item), 'set' => time()];
                }
                return $saved;
        }

        public function deleteItem($key) {
                $result = parent::deleteItem($key);
                if ($result && array_key_exists($key, $_SESSION[$this->sessionKey])) {
                        unset($_SESSION[$this->sessionKey][$key]);
                }
                return $result;
        }

        public function clear() {
                $result = parent::clear();
                if ($result) {
                        $_SESSION[$this->sessionKey] = [];
                }
                return $result;
        }

        public function getItem($key) {
                $item = parent::getItem($key);
                if (array_key_exists($key, $_SESSION[$this->sessionKey])) {
                        $stored = unserialize($_SESSION[$this->sessionKey][$key]['item']);
                        $time = $_SESSION[$this->sessionKey][$key]['set'];
                        if ($this->expireTime > 0 && $time + $this->expireTime < time()) {
                                $this->deleteItem($key);
                                $item = new Item($key);
                                $this->saveDeferred($item);
                        }
                        else if ($item->get() !== $stored->get()) {
                                $item->set($stored->get());
                                $item->expiresAt($time);
                        }
                }
                return $item;
        }

}
