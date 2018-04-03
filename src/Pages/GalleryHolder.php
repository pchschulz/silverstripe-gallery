<?php

namespace PaulSchulz\SilverStripe\Gallery\Pages;
use SilverStripe\CMS\Model\SiteTree;

/**
 * This is the holder class for gallery pages.
 * @package PaulSchulz\SilverStripe\GalleryExtension\Pages
 */
class GalleryHolder extends \Page {
    private static $table_name = 'GalleryHolder';

    private static $allowed_children = [
        GalleryPage::class
    ];

    /**
     * Returns all children of this page, which the user can view.
     * This function is necessary, because the children should not be shown in the menus.
     * @return \SilverStripe\ORM\ArrayList
     */
    public function ShowGalleries() {
        return $this->AllChildren()->filterByCallback(function(SiteTree $page) {
            return $page->canView();
        });
    }
}