<?php

/**
 * SessionCache file
 *
 * Copyright (c) 2016, Kiril Savchev
 * All rights reserved.
 *
 * @category Libs
 * @package Cache
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 *
 * @license http://www.apache.org/licenses/ Apache License 2.0
 * @link http://ifthenelse.info
 */
namespace Ite\Cache;

use Ite\Cache\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

/**
 * SessionCache
 *
 * This class uses sessions to store values
 *
 * @version 1.0
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class SessionCache extends AbstractPool {

        /**
         * The session key, e.g. the $_SESSION array index
         *
         * @var string
         */
        protected $sessionKey;

        /**
         * Creates new session cache storage
         *
         * @param string $sessionKey [Optional] The session index for storing values
         * @param int|\DateInterval|null $expireTime [Optional] The expire time
         * @throws InvalidArgumentException
         */
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

        /**
         * Saves values to the session
         *
         * This method stores only the value. The parent method from the
         * AbstractPool class stores the item object into the inner container.
         *
         * @param CacheItemInterface $item The cache item to be stored
         * @return bool True on success, otherwise false
         */
        public function save(CacheItemInterface $item) {
                $saved = parent::save($item);
                if ($saved) {
                        $_SESSION[$this->sessionKey][$item->getKey()] = ['item' => serialize($item), 'set' => time()];
                }
                return $saved;
        }

        /**
         * Deletes a value from the session storage
         *
         * This method deletes the item's value from the session storage and its
         * parent's method deleteItem() deletes the item with this key from the
         * inner storage
         *
         * @param string $key The item key
         * @return bool True on success, otherwise false
         */
        public function deleteItem($key) {
                $result = parent::deleteItem($key);
                if ($result && array_key_exists($key, $_SESSION[$this->sessionKey])) {
                        unset($_SESSION[$this->sessionKey][$key]);
                }
                return $result;
        }

        /**
         * Clears the session storage
         *
         * @return bool True on success, otherwise false
         */
        public function clear() {
                $result = parent::clear();
                if ($result) {
                        $_SESSION[$this->sessionKey] = [];
                }
                return $result;
        }

        /**
         * Retrieve an item from the cache storage
         *
         * The parent method getItem() retrieves the item object and this method
         * gets its value from session storage if there is any. If there is not
         * such key in the session storage it will be created as a deffered item
         *
         * @param string $key The item key
         * @return \Ite\Cache\Item The found or newly created item
         */
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
