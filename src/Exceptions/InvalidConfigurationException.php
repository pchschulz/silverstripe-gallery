<?php

namespace PaulSchulz\SilverStripe\Gallery\Exceptions;

use RuntimeException;

/**
 * This exception is thrown to indicate the configuration in the .yml files or directly in php classes in invalid.
 * F.e. you try to apply a negative margin on a gallery image.
 *
 * @package PaulSchulz\SilverStripe\Gallery\Exceptions
 */
class InvalidConfigurationException extends RuntimeException {

}