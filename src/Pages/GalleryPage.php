<?php

namespace PaulSchulz\SilverStripe\Gallery\Pages;

use Page;
use PaulSchulz\SilverStripe\Gallery\Extensions\SiteTreeGalleryExtension;

/**
 * This is a page representing a gallery. The GalleryExtension is applied to this class.
 *
 * @package PaulSchulz\SilverStripe\Gallery\Pages
 * @mixin SiteTreeGalleryExtension
 */
class GalleryPage extends Page {
	private static $extensions = [
		SiteTreeGalleryExtension::class,
	];

    private static $table_name = 'GalleryPage';

    private static $defaults = [
        'ShowInMenus' => false
    ];

    private static $description = 'A page which shows images in a nice form';
}