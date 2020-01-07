<?php

namespace PaulSchulz\SilverStripe\Gallery\Views;

use PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData;

/**
 * This class represents an image line in an image collection.
 * It contains a list of images which are rendered to one line in the template.
 *
 * @package PaulSchulz\SilverStripe\GalleryExtension\Views
 */
class ImageLine extends ViewableData {
    /**
     * The desired height for this image line. The actually height can be slightly different through the calculation process.
     * @var int
     */
    private $desiredHeight;

    /**
     * The width this image line is optimized for.
     * @var int
     */
    private $optimizedWidth;

    /**
     * All images of this line.
     * @var ArrayList
     */
    private $images;

    /**
     * This is set to true, if this line is the first line in an image line collection.
     * This is necessary, because the first line in a gallery should not have margin at the top applied.
     * @var bool
     */
    private $firstLine;

    /**
     * ImageLine constructor.
     * @param int $desiredHeight
     * @param int $optimizedWidth
     * @param bool $firstLine
     */
    public function __construct($desiredHeight, $optimizedWidth, bool $firstLine = false) {
        parent::__construct();

        $this->images = new ArrayList();
        $this->desiredHeight = $desiredHeight;
        $this->optimizedWidth = $optimizedWidth;
        $this->firstLine = $firstLine;
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
     * @throws InvalidConfigurationException
     * @return bool
     */
    public function hasEnoughSpace(GalleryImage $image) : bool {
        return $this->isEmpty() || $this->getWidth() + GalleryImage::getMargin() + $image->getScaledWidth() <= $this->getOptimizedWidth();
    }

    /**
     * Returns true if the line is empty.
     * @return bool
     */
    public function isEmpty() : bool {
        return $this->images->count() === 0;
    }

    /**
     * Returns the desired height for this image line. The actually height can be slightly different through the calculation process.
     * This property is set in the constructor.
     * @return int
     */
    public function getDesiredHeight() : int {
        return $this->desiredHeight;
    }

    /**
     * Returns the optimized width for this image line.
     * This property is set in the constructor.
     * @return int
     */
    public function getOptimizedWidth() : int {
        return $this->optimizedWidth;
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
     * @throws InvalidConfigurationException
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
     * @throws InvalidConfigurationException
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
     * @throws InvalidConfigurationException
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
     * @throws InvalidConfigurationException
     * @see getOptimizedWidth()
     */
    public function match() {
        if (!$this->isEmpty()) {
            $resizeFactor = ($this->getOptimizedWidth() - $this->getAllImagesRightMargin()) / $this->getWidthWithoutMargin();
            foreach ($this->images as $image) {
                /** @var GalleryImage $image */
                $image->scale($resizeFactor);
            }
        }
    }

    /**
     * Returns the deviation to the desired height specified by $this->getDesiredHeight().
     * @see getDesiredHeight()
     * @return float
     */
    public function getBiasFromDesiredHeight() : float {
        return abs($this->getHeightWithoutMargin() - $this->getDesiredHeight());
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
     * @throws InvalidConfigurationException
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function forTemplate() {
        $width = $this->getWidth();

        foreach ($this->images as $image) {
            /** @var GalleryImage $image */
            $image->setLineWidth($width);
            $image->setHasMarginTop(!$this->firstLine);
        }

        if ($lastImage = $this->images->last()) {
            /** @var GalleryImage $lastImage */
            $lastImage->setHasMarginRight(false);
        }

        return $this->renderWith(self::class);
    }
}