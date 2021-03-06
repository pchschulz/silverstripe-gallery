<?php

namespace PaulSchulz\SilverStripe\Gallery\Pages;
use Page;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\SS_List;

/**
 * This is the holder class for gallery pages.
 *
 * @package PaulSchulz\SilverStripe\GalleryExtension\Pages
 */
class GalleryHolder extends Page {
    private static $table_name = 'GalleryHolder';

    private static $allowed_children = [
        GalleryPage::class
    ];

    private static $description = 'An overview page for all subordinated galleries.';

    /**
     * Returns all children of this page, which the user can view.
     * This function is necessary, because the children should not be shown in the menus.
     * @return SS_List
     */
    public function ShownGalleries(): SS_List {
        return $this->AllChildren()->filterByCallback(function(SiteTree $page) {
            return $page->canView();
        });
    }
}