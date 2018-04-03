<?php

namespace PaulSchulz\SilverStripe\Gallery\Extensions;

use PaulSchulz\SilverStripe\Gallery\Models\GalleryImage;
use PaulSchulz\SilverStripe\Gallery\Views\ImageLine;
use PaulSchulz\SilverStripe\Gallery\Views\ImageLineCollection;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;

/**
 * This extension is responsible for creating the appearance of the gallery.
 * It can be applied to any DataObject, to support listing of images.
 * This extension only provides support for listing the images of a gallery. For a more advanced gallery see the subclass GalleryExtension.
 * When applied to a DataObject the cms fields must be created by the object itself. This class does not create any cms fields.
 * @package PaulSchulz\SilverStripe\GalleryExtension\Models
 * @see GalleryExtension
 * @property string BiasMode
 * @property ImageCollectionExtension|DataObject owner
 * @method ManyManyList Images
 */
class ImageCollectionExtension extends DataExtension {
    private static $db = [
        'BiasMode' => "Enum('avg,max','avg')",
    ];

    private static $many_many = [
        'Images' => GalleryImage::class
    ];

    private static $many_many_extraFields = [
        'Images' => [
            'Sort' => 'Int'
        ]
    ];

    /**
     * Returns the images in the correct order specified by the Sort int.
     * @return DataList
     */
    public function SortedImages() : DataList {
        return $this->owner->Images()->sort('Sort', 'ASC');
    }

    /**
     * This function returns the images with the best combination of lines, calculated by findBestImageOrder().
     * This function automatically sorts the images.
     * @see findBestImageOrder()
     * @throws \PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException
     * @return ImageLineCollection
     */
    public function AdjustImages() : ImageLineCollection {
        $images = new ArrayList($this->owner->SortedImages()->toArray());

        //put all images to the desired height
        foreach ($images as $image) {
            /** @var GalleryImage $image */
            $image->setScaleByHeight(ImageLine::getDesiredHeight());
        }

        return $this->owner->findBestImageOrder(new ArrayList($images->toArray()));
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
     * @throws \PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException
     * @return ImageLineCollection
     */
    public function findBestImageOrder(SS_List $images, bool $firstCall = true) : ImageLineCollection {
        //if list is empty return an empty ImageLineCollection
        if ($images->count() === 0) {
            return new ImageLineCollection(new ArrayList(), $this->owner->BiasMode);
        }

        //an ImageLine object is created and is filled with images until it is full (hasEnoughSpace() return false)
        $line = new ImageLine($firstCall);
        foreach ($images as $image) {
            /** @var GalleryImage $image */
            if (!$line->hasEnoughSpace($image)) {
                //if line is full break
                break;
            }

            $line->addImage($image);
            $images->remove($image);
        }

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
     * Returns a description for all bias modes. Useful for description of cms fields.
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
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
        ])->renderWith('PaulSchulz\SilverStripe\Gallery\Includes\BiasModeDescription');
    }

    /**
     * This function is called after this object was saved.
     * It publishes all images of this image collection.
     */
    public function onAfterWrite() {
        parent::onAfterWrite();

        foreach ($this->owner->Images() as $image) {
            /** @var GalleryImage $image */
            if (!$image->isPublished()) {
                $image->publishSingle();
            }
        }
    }
}