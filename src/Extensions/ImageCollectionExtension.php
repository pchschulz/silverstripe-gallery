<?php

namespace PaulSchulz\SilverStripe\Gallery\Extensions;

use Bummzack\SortableFile\Forms\SortableUploadField;
use PaulSchulz\SilverStripe\Gallery\Exceptions\IllegalOwnerException;
use PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException;
use PaulSchulz\SilverStripe\Gallery\Views\GalleryImage;
use PaulSchulz\SilverStripe\Gallery\Views\ImageLine;
use PaulSchulz\SilverStripe\Gallery\Views\ImageLineCollection;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;

/**
 * This extension is responsible for creating the appearance of the gallery.
 * It can be applied to any DataObject, to support listing of images.
 * This extension only provides support for listing the images of a gallery. For a more advanced gallery see the subclass GalleryExtension.
 * When applied to a DataObject the cms fields must be created by the object itself. This class does not create any cms fields.
 *
 * @package PaulSchulz\SilverStripe\GalleryExtension\Models
 * @see GalleryExtension
 * @property ImageCollectionExtension|DataObject owner
 * @method ManyManyList Images
 */
class ImageCollectionExtension extends DataExtension {
    private static $db = [
    	'QuickModeActivated' => 'Boolean',
        'BiasMode' => "Enum('avg,max','avg')",
    ];

    private static $many_many = [
        'Images' => Image::class
    ];

    private static $many_many_extraFields = [
        'Images' => [
            'Sort' => 'Int'
        ]
    ];

    private static $owns = [
    	'Images',
	];

    /**
     * This function returns a Config object for the owner of this class.
     * @return Config_ForClass
     * @throws IllegalOwnerException Thrown if this class does not use the Configurable trait.
     */
    protected function getOwnerConfig() : Config_ForClass {
        $ownerClass = get_class($this->owner);

        //ensure the owner class uses the Configurable trait
        //use method_exists, because instanceof does not work
        if (method_exists($ownerClass, 'config')) {
            $config = $ownerClass::config();
            if ($config instanceof Config_ForClass) {
                return $config;
            }
        }

        throw new IllegalOwnerException('The class ' . $ownerClass . ' does not use the SilverStripe\Core\Config\Configurable trait.');
    }

    /**
     * Returns the desired height a line should have. The actually height can be slightly different through the calculation process.
     * This height can be specified in the config.yml.
     * @return int
     * @throws IllegalOwnerException
     * @throws InvalidConfigurationException
     */
    public function getDesiredHeight() : int {
        $desiredHeight = $this->getOwnerConfig()->get('desired_height');
        if ($desiredHeight <= 0) {
            throw new InvalidConfigurationException('The desired height must be greater or equal to 0.');
        }
        if ((int) $desiredHeight != $desiredHeight) {
            throw new InvalidConfigurationException('Decimals as a value for the desired height are not allowed.');
        }

        return $desiredHeight;
    }

    /**
     * Returns the optimized width a line has.
     * This width can be specified in the config.yml.
     * @return int
     * @throws IllegalOwnerException
     * @throws InvalidConfigurationException
     */
    public function getOptimizedWidth() : int {
        $optimizedWidth = $this->getOwnerConfig()->get('optimized_width');

        if ($optimizedWidth <= 0) {
            throw new InvalidConfigurationException('The optimized width must be greater or equal to 0.');
        }
        if ($optimizedWidth < GalleryImage::getMargin()) {
            throw new InvalidConfigurationException('The optimized width must be greater or equal to the configured margin of a gallery image.');
        }
        if ((int) $optimizedWidth != $optimizedWidth) {
            throw new InvalidConfigurationException('Decimals as a value for the optimized width are not allowed.');
        }

        return $optimizedWidth;
    }

    /**
     * Returns the images in the correct order specified by the Sort int.
     * @return DataList
     */
    public function SortedImages() : DataList {
        return $this->owner->Images()->sort('Sort', 'ASC');
    }

	/**
	 * Wraps the images of this DataObject into a GalleryImage object for further processing.
	 * A list with all gallery images is returned.
	 * @return SS_List
	 */
    public function getGalleryImages(): SS_List {
    	$list = new ArrayList();
    	foreach ($this->SortedImages() as $image) {
    		$list->add(new GalleryImage($image));
		}

    	return $list;
	}

    /**
     * This function returns the images with the best combination of lines, calculated by findBestImageOrder().
     * This function automatically sorts the images.
     * @see findBestImageOrder()
     * @return ImageLineCollection
     */
    public function AdjustImages() : ImageLineCollection {
        $images = $this->owner->getGalleryImages();

        //put all images to the desired height
        foreach ($images as $image) {
            /** @var GalleryImage $image */
            $image->setScaleByHeight($this->getDesiredHeight());
        }

        $imagesList = new ArrayList($images->toArray());
        if ($this->owner->QuickModeActivated) {
        	return $this->findQuickImageOrder($imagesList);
		}

        return $this->owner->findBestImageOrder($imagesList);
    }

	/**
	 * Returns an ImageLineCollection with all images, which are put in lines.
	 * This function is a quick way to align the images across different lines.
	 * This algorithm does not calculate the perfect combination. Instead it just puts images into lines until it is full.
	 * This algorithm is much faster.
	 * @param SS_List $images
	 * @return ImageLineCollection
	 */
    public function findQuickImageOrder(SS_List $images) : ImageLineCollection {
    	$lines = new ArrayList();

    	$firstCall = true;
    	while ($images->count() !== 0) {
    		$currentLine = $this->putImagesToLine($images, $firstCall);
    		$currentLine->match();

    		$lines->push($currentLine);

    		$firstCall = false;
		}

    	return new ImageLineCollection($lines, $this->owner->BiasMode);
	}

    /**
     * Returns an ImageLineCollection with all images, which are put in lines.
     * This function returns the perfect combination of all images, so that the desired height specified in the config.yml only is slightly different,
     * but each line has the same width.
     * There are two algorithms (modes), which can be used to determine the best image order:
     *  - avg (This keeps attention on the average difference to the desired height)
     *  - max (This searches for the maximum difference to the desired height of a line and takes this to find the best order. This mode better prevents very large lines.)
     * @param SS_List $images
     * @param bool $firstCall
     * @return ImageLineCollection
     */
    public function findBestImageOrder(SS_List $images, bool $firstCall = true) : ImageLineCollection {
        //if list is empty return an empty ImageLineCollection
        if ($images->count() === 0) {
            return new ImageLineCollection(new ArrayList(), $this->owner->BiasMode);
        }

        $line = $this->putImagesToLine($images, $firstCall);

        /*
         * Now there are two possibilities for the best result for this line:
         *  - to leave the line as it is, so all further images must be put into separate lines
         *  - to pull the next image into the current line, too
         * This depends on which method results in the lower deviation to the desired line height.
         * The calculations of the other lines are done by a recursive call of this function (each call returns the best combination for the images left).
         */
        if ($images->count() !== 0) {
            //clone all images left, because they are needed in both recursive calls. Without cloning the second call would modify the result of the first call.
            $clonedImages = new ArrayList();
            foreach ($images as $image) {
                $clonedImages->push(clone $image);
            }

            //clone current line for second calculation
            $secondLine = clone $line;

            //clone this image before the calculation with the image in the next line starts, because the image could be modified there.
            $nextImage = clone $images->first();

            //recursive calculation with next image in next line
            $resultOne = $this->owner->findBestImageOrder($clonedImages, false);
            $line->match();

            //clone the current line and put the next image in this line, too
            $secondLine->addImage($nextImage);
            $secondLine->match();
            $images->remove($images->first());

            if ($images->count() === 0) {
                $resultTwo = new ImageLineCollection(new ArrayList([$secondLine]), $this->owner->BiasMode);
            }
            else {
                //recursive calculation with next image in the current line and merge the results with the calculations of the current line
                $resultTwo = $this->owner->findBestImageOrder($images, false);
                $resultTwo->addLine($secondLine, true);
            }

            //merge the results of the first recursive call with the calculations of the current line
            $resultOne->addLine($line, true);

            //compare the two results (which has the lower bias)
            return $resultOne->getBias() < $resultTwo->getBias() ? $resultOne : $resultTwo;
        }

        //return the current line if no more images left
        $line->match();
        return new ImageLineCollection(new ArrayList([$line]), $this->owner->BiasMode);
    }

	/**
	 * Puts as many images from $images into one line as they can fit into the line.
	 * One line contain at least on image.
	 * The images put to a line are removed from $images afterwards.
	 * @param SS_List $images
	 * @param bool $firstCall
	 * @return ImageLine
	 */
    public function putImagesToLine(SS_List $images, $firstCall = true) {
		//an ImageLine object is created and is filled with images until it is full (hasEnoughSpace() return false)
		$line = new ImageLine($this->getDesiredHeight(), $this->getOptimizedWidth(), $firstCall);
		foreach ($images as $image) {
			/** @var GalleryImage $image */
			if (!$line->hasEnoughSpace($image)) {
				//if line is full break
				break;
			}

			$line->addImage($image);
			$images->remove($image);
		}

		return $line;
	}

    /**
     * Updates the cms fields in $fields.
     * This function add all field to a tab named 'Gallery'.
	 * Adds a checkbox for activating the quick mode.
     * Adds a dropdown field for choosing the bias mode.
     * Adds a sortable upload field for uploading and sorting the images of this image collection.
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields) {
        if (!$fields->fieldByName('Root.Gallery')) {
            $fields->addFieldToTab('Root', new Tab('Gallery', _t(self::class . '.GALLERY_TAB', 'Gallery')));
        }

        //remove default Images tab
        $fields->removeByName('Images');

        $fields->addFieldsToTab('Root.Gallery', [
        	CheckboxField::create('QuickModeActivated', _t(self::class . '.db_QuickMode', 'Quick mode'))
				->setDescription(_t(self::class . '.QUICK_MODE_DESCRIPTION', 'The quick mode is not so hard to calculate, so that this mode is fast, but may not provide perfect results.')),
            DropdownField::create('BiasMode', _t(self::class . '.db_BiasMode', 'Bias mode'), $this->owner->dbObject('BiasMode')->enumValues())
                ->setDescription($this->getBiasModeDescription()),
            SortableUploadField::create('Images', _t(self::class . '.many_many_Images', 'Images'))
                ->setSortColumn('Sort')
                ->setFolderName('galleries')
        ]);
    }

    /**
     * Returns a description for all bias modes. Useful for description of cms fields.
     * @return DBHTMLText
     */
    public function getBiasModeDescription() {
        $values = new ArrayList();
        foreach ($this->owner->dbObject('BiasMode')->enumValues() as $value) {
            $defaultDescription = '';
            switch ($value) {
                case 'avg':
                    $defaultDescription = 'The average bias of all lines (The height of the rows sticks mostly closer to the desired height. Mostly the preferred option)';
                    break;
                case 'max':
                    $defaultDescription = 'The greatest deviation of all lines (Better prevents too height lines)';
                    break;
                default:
                    trigger_error('Missing translation default for bias mode \'' . $value . '\'.');
            }

            $values[] = new ArrayData([
                'Title' => $value,
                'Explanation' => _t(self::class . '.BIAS_MODE_EXPLANATION_' . strtoupper($value), $defaultDescription),
            ]);
        }

        return $this->owner->customise([
            'Hint' => _t(self::class . '.BIAS_MODE_EXPLANATION', 'The "Bias mode" determines how the deviation from the desired line height is determined.'),
            'Values' => $values,
        ])->renderWith('PaulSchulz\SilverStripe\Gallery\Extensions\Includes\BiasModeDescription');
    }
}