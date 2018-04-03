<?php

namespace PaulSchulz\SilverStripe\Gallery\Extensions;
use SilverStripe\ORM\DataObject;

/**
 * This is the extension for creating a gallery with more information like location and description.
 * This extension is also applied to GalleryPage.
 * @package PaulSchulz\SilverStripe\GalleryExtension\Models
 * @property string Title
 * @property string Date
 * @property string Location
 * @property string Content
 * @property string int
 */
class GalleryExtension extends ImageCollectionExtension {
    private static $db = [
        'Title' => 'Varchar(255)',
        'Date' => 'Date',
        'Location' => 'Varchar',
        'Content' => 'HTMLText',
    ];

    /**
     * Returns a preview image for this gallery. This is the first image of all images, which are sorted.
     * @return DataObject
     */
    public function PreviewImage() : DataObject {
        return $this->owner->SortedImages()->first();
    }
}