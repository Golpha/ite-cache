<?php

namespace Ite\Cache;

use Psr\Cache\CacheItemPoolInterface;

/**
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
interface CachePoolInterface extends CacheItemPoolInterface {

        /**
         *
         * @param null|int|\DateInterval $expireTime
         */
        public function setExpireTime($expireTime);

}
