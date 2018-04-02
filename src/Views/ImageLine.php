<?php

namespace PaulSchulz\SilverStripe\Gallery\Views;

use PaulSchulz\SilverStripe\Gallery\Models\GalleryImage;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData;

/**
 * Class ImageLine
 * @package PaulSchulz\SilverStripe\GalleryExtension\Views
 */
class ImageLine extends ViewableData {
    /**
     * @var ArrayList
     */
    private $images;

    /**
     * @var bool
     */
    private $firstLine;

    /**
     * ImageLine constructor.
     * @param bool $firstLine
     */
    public function __construct(bool $firstLine = false) {
        $this->images = new ArrayList();
        $this->firstLine = $firstLine;
    }

    /**
     * Returns the width a line should be optimized for.
     * This width can be specified in the config.yml.
     * @return int
     */
    public static function getOptimizedWidth() : int {
        return self::config()->get('optimized_width');
    }

    /**
     * Returns the desired height a line should have. This height can be slightly different through the calculation process.
     * This height can be specified in the config.yml.
     * @return int
     */
    public static function getDesiredHeight() : int {
        return self::config()->get('desired_height');
    }

    /**
     * Adds an image to this line.
     * @param GalleryImage $image
     */
    public function addImage(GalleryImage $image) {
        $this->images[] = $image;
    }

    /**
     * Test if there is enough space for $image left in this image line.
     * This function always returns true if the line is empty.
     * @param GalleryImage $image
     * @return bool
     */
    public function hasEnoughSpace(GalleryImage $image) : bool {
        return $this->isEmpty() || $this->getWidth() + GalleryImage::getMargin() + $image->getScaledWidth() <= static::getOptimizedWidth();
    }

    /**
     * Returns true if the line is empty.
     * @return bool
     */
    public function isEmpty() : bool {
        return $this->images->count() === 0;
    }

    /**
     * Returns the current width of this line without margin, calculated by add up the width and the margin of all images of this line.
     * @return float
     */
    public function getWidthWithoutMargin() : float {
        $width = 0;
        foreach ($this->images as $image) {
            /** @var GalleryImage $image */
            $width += $image->getScaledWidth();
        }

        return $width;
    }

    /**
     * Returns the current width of this line, calculated by add up the width and the margin of all images of this line.
     * This is not the width specified in the config.yml.
     * @return float
     */
    public function getWidth() : float {
        return $this->getWidthWithoutMargin() + $this->getAllImagesRightMargin();
    }

    /**
     * Returns the height of this line, calculated by searching for the highest image.
     * @return float
     */
    public function getHeightWithoutMargin() : float {
        $height = 0;
        foreach ($this->images as $image) {
            /** @var GalleryImage $image */
            if (($currentHeight = $image->getScaledHeight()) > $height) {
                $height = $currentHeight;
            }
        }

        return $height;
    }

    /**
     * Returns the height of this line, calculated by searching for the highest image.
     * This line height includes the margin, if any.
     * @return float
     */
    public function getHeight() : float {
        $height = $this->getHeightWithoutMargin();

        if (!$this->firstLine) {
            $height += GalleryImage::getMargin();
        }

        return $height;
    }

    /**
     * Returns the sum of the margins of all images of this line.
     * @return int
     */
    public function getAllImagesRightMargin() : int {
        if ($this->images->count() === 0) {
            return 0;
        }

        return ($this->images->count() - 1) * GalleryImage::getMargin();
    }

    /**
     * Match the images to the line, so that the complete space of the line is used and the height of all images is equal afterwards.
     * The space is specified by $this->getOptimizedWidth().
     * @see getOptimizedWidth()
     */
    public function match() {
        $resizeFactor = $this->getWidthWithoutMargin() / (static::getOptimizedWidth() - $this->getAllImagesRightMargin());
        foreach ($this->images as $image) {
            /** @var GalleryImage $image */
            $image->scale($resizeFactor);
        }
    }

    /**
     * Returns the deviation to the desired height specified by $this->getDesiredHeight().
     * @see getDesiredHeight()
     * @return float
     */
    public function getBiasFromDesiredHeight() : float {
        return abs($this->getHeightWithoutMargin() - static::getDesiredHeight());
    }

    /**
     * Returns all images of this image line.
     * @return ArrayList
     */
    public function getImages(): ArrayList {
        return $this->images;
    }

    /**
     * This magic function is called when this object is cloned.
     * It creates a deep copy of this object.
     */
    public function __clone() {
        $oldList = $this->images;
        $this->images = new ArrayList();

        foreach ($oldList as $image) {
            /** @var GalleryImage $image */
            $this->images[] = clone $image;
        }
    }

    /**
     * This function is called when this object should be rendered to a template.
     * Additionally this function calculates the width of the images of this line in percent for responsive purposes.
     * This function also determines if the images of this line should have margin at the top or not.
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function forTemplate() {
        $width = $this->getWidth();

        foreach ($this->images as $image) {
            /** @var GalleryImage $image */
            $image->setPercentageWidth($image->getScaledWidth() / $width * 100);
            $image->setHasMarginTop(!$this->firstLine);
        }

        if ($lastImage = $this->images->last()) {
            /** @var GalleryImage $lastImage */
            $lastImage->setHasMarginRight(false);
        }

        return $this->renderWith(self::class);
    }
}