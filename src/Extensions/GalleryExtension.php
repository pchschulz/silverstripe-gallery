<?php

namespace PaulSchulz\SilverStripe\Gallery\Extensions;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
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
     * @return DataObject|null
     */
    public function PreviewImage() {
        return $this->owner->SortedImages()->first();
    }

    /**
     * Updates the cms field in $fields.
     * Adds a date field and a field for the location to the gallery tab created by updateCMSFields in ImageCollectionExtension.
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields) {
        parent::updateCMSFields($fields);

        $fields->addFieldsToTab('Root.Gallery', [
            new DateField('Date', _t(self::class . '.db_Date', 'Date')),
            new TextField('Location', _t(self::class . '.db_Location', 'Location')),
        ], 'BiasMode');
    }
}