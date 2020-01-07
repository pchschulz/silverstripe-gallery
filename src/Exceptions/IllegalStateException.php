<?php

namespace PaulSchulz\SilverStripe\Gallery\Exceptions;

use RuntimeException;

/**
 * This exception is thrown, when a method is called on object, but the object is not ready to execute this function.
 * So this represents an illegal state.
 *
 * @package PaulSchulz\SilverStripe\GalleryExtension\Exceptions
 */
class IllegalStateException extends RuntimeException {

}