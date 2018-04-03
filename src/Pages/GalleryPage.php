<?php

namespace PaulSchulz\SilverStripe\Gallery\Pages;

use Bummzack\SortableFile\Forms\SortableUploadField;
use PaulSchulz\SilverStripe\Gallery\Extensions\GalleryExtension;
use PaulSchulz\SilverStripe\Gallery\Extensions\ImageCollectionExtension;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;

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

    /**
     * Returns the cms fields for this object.
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root', new Tab(
            'Gallery',
            _t(self::class . '.GALLERY_TAB', 'Gallery'),
            new DateField('Date', _t(GalleryExtension::class . '.db_Date', 'Date')),
            new TextField('Location', _t(GalleryExtension::class . '.db_Location', 'Location')),
            DropdownField::create('BiasMode', _t(ImageCollectionExtension::class . '.db_BiasMode', 'Bias mode'), $this->dbObject('BiasMode')->enumValues())
                ->setDescription($this->getBiasModeDescription()),
            SortableUploadField::create('Images', _t(ImageCollectionExtension::class . '.many_many_Images', 'Images'))
                ->setSortColumn('Sort')
                ->setFolderName('galleries')
        ));

        return $fields;
    }
}