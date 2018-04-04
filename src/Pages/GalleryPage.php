<?php

namespace PaulSchulz\SilverStripe\Gallery\Pages;

use PaulSchulz\SilverStripe\Gallery\Extensions\GalleryExtension;

/**
 * This is a page representing a gallery. The GalleryExtension is applied to this class.
 * @package PaulSchulz\SilverStripe\Gallery\Pages
 * @mixin GalleryExtension
 */
class GalleryPage extends \Page {
    private static $table_name = 'GalleryPage';

    private static $defaults = [
        'ShowInMenus' => false
    ];
}