<?php

namespace Ite\Cache\Exception;

use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionInterface;

/**
 * InvalidArgumentException
 *
 * @author Kiril Savchev <k.savchev@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException implements InvalidArgumentExceptionInterface {

}
