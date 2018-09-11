<?php

namespace PaulSchulz\SilverStripe\Gallery\Models;

use InvalidArgumentException;
use PaulSchulz\SilverStripe\Gallery\Exceptions\IllegalStateException;
use PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * This class represents an image of an image collection or a gallery.
 * All operations done on this object, like setScaleByWidth(), are not applied to the underlying image.
 * Instead $this->scale is changed, to just calculate the dimensions of this image for performance reasons.
 * @package PaulSchulz\SilverStripe\GalleryExtension\Models
 */
class GalleryImage extends Image {
    /**
     * The scale factor of this image.
     * @var float
     */
    protected $scale = 1;

    /**
     * The width of the image line this image is wrapped in.
     * This property is only set, when this object is rendered to a template, so when forTemplate() of the ImageLine class is called.
     * @var int
     */
    protected $lineWidth = 0;

    /**
     * Determines if this image is rendered with margin at the top or not.
     * This property is only set, when this object is rendered to a template, so when forTemplate() of the ImageLine class is called.
     * @var bool
     */
    protected $hasMarginTop = true;

    /**
     * Determines if this image is rendered with margin at the right or not.
     * This property is only set, when this object is rendered to a template, so when forTemplate() of the ImageLine class is called.
     * @var bool
     */
    protected $hasMarginRight = true;

    /**
     * Returns the margin of an image. The margin is applied on the top and at the right of the image.
     * This margin can be set in config.yml.
     * @throws InvalidConfigurationException
     * @return int
     */
    public static function getMargin() : int {
        $margin = self::config()->get('margin');
        if ($margin < 0) {
            throw new InvalidConfigurationException('A negative margin is no allowed.');
        }
        if ((int) $margin != $margin) {
            throw new InvalidConfigurationException('Decimals as a value for the optimized width are not allowed.');
        }

        return $margin;
    }

    /**
     * Sets the line width for the
     * @param $lineWidth
     */
    public function setLineWidth($lineWidth) {
        if ($lineWidth <= 0) {
            throw new InvalidArgumentException('The $lineWidth must be greater or equal to 0.');
        }

        $this->lineWidth = $lineWidth;
    }

    /**
     * Sets the scale by the height of the image.
     * This function does not touch the real image. It just sets $this->scale, for calculation reasons.
     * @param float $height
     */
    public function setScaleByHeight(float $height) {
        if ($height < 0) {
            throw new \InvalidArgumentException('A negative image height is not allowed.');
        }

        $this->scale *= $height / ($this->getHeight() * $this->scale);
    }

    /**
     * Sets the scale by the width of the image.
     * This function does not touch the real image. It just sets $this->scale, for calculation reasons.
     * @param float $width
     */
    public function setScaleByWidth(float $width) {
        if ($width < 0) {
            throw new \InvalidArgumentException('A negative image width is not allowed.');
        }

        $this->scale *= $width / ($this->getWidth() * $this->scale);
    }

    /**
     * Sets the value of $this->addMarginTop.
     * This is necessary to determine if the margin should be added to the top of the image in the template.
     * @param bool $hasMarginTop
     */
    public function setHasMarginTop(bool $hasMarginTop) {
        $this->hasMarginTop = $hasMarginTop;
    }

    /**
     * Sets the value of $this->addMarginRight.
     * This is necessary to determine if the margin should be added to the right of the image in the template.
     * @param bool $hasMarginRight
     */
    public function setHasMarginRight(bool $hasMarginRight) {
        $this->hasMarginRight = $hasMarginRight;
    }

    /**
     * Returns the value $this->addMarginTop.
     * This is necessary to determine if the margin should be added to the top of the image in the template.
     * @return bool
     */
    public function HasMarginTop(): bool {
        return $this->hasMarginTop;
    }

    /**
     * Sets the value of $this->addMarginRight.
     * This is necessary to determine if the margin should be added to the right of the image in the template.
     * @return bool
     */
    public function HasMarginRight(): bool {
        return $this->hasMarginRight;
    }

    /**
     * Scales this image dimensions about the factor $scale.
     * @param float $scale
     */
    public function scale(float $scale) {
        if ($scale < 0) {
            throw new \InvalidArgumentException('A negative scale factor is not allowed');
        }

        $this->scale *= $scale;
    }

    /**
     * Returns the height of the image based on $this->scale.
     * @return float
     */
    public function getScaledHeight() : float {
        return $this->getHeight() * $this->scale;
    }

    /**
     * Returns the height of the image based on $this->scale.
     * @return float
     */
    public function getScaledWidth() : float {
        return $this->getWidth() * $this->scale;
    }

    /**
     * This function returns the value in $size as percentage of $this->lineWidth.
     * This is just a helper function for other function in this class.
     * @param $size
     * @return float
     */
    protected function getPercentageOfLineWidth($size) : float {
        if ($this->lineWidth === 0) {
            throw new IllegalStateException('The fields $this->lineWidth must not be 0, when calling this function.');
        }

        return $size / $this->lineWidth * 100;
    }

    /**
     * Returns the width in percent of the wrapping line.
     * This property is only set when a line is rendered to a template.
     * @return float
     */
    public function getPercentageWidth() : float {
        return $this->getPercentageOfLineWidth($this->getScaledWidth());
    }

    /**
     * Returns the margin of this image in percent of the width of one image line.
     * @throws InvalidConfigurationException
     * @return float
     */
    public function getPercentageMargin() : float {
        return $this->getPercentageOfLineWidth(static::getMargin());
    }

    /**
     * This function is called when this object should be rendered to a template.
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|string
     */
    public function forTemplate() : DBHTMLText {
        return $this->renderWith(self::class);
    }
}