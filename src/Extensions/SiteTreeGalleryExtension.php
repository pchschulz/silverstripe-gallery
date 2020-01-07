<?php

namespace PaulSchulz\SilverStripe\Gallery\Extensions;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
 * This class is similar to the GalleryExtension class.
 * The only difference is that this class should only be used for page deriving from SiteTree.
 * The Title and Content fields have been removed from this extension to prevent duplicate database fields.
 *
 * @package PaulSchulz\SilverStripe\Gallery\Extensions
 */
class SiteTreeGalleryExtension extends ImageCollectionExtension {
	private static $db = [
		'Date' => 'Date',
		'Location' => 'Varchar',
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