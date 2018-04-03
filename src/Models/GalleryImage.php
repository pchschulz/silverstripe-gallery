<?php

namespace PaulSchulz\SilverStripe\Gallery\Models;

use PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException;
use PaulSchulz\SilverStripe\Gallery\Views\ImageLine;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class GalleryImage
 * @package PaulSchulz\SilverStripe\GalleryExtension\Models
 */
class GalleryImage extends Image {
    /**
     * The scale factor of this image.
     * @var float
     */
    protected $scale = 1;

    /**
     * @var float
     */
    protected $percentageWidth;

    /**
     * @var bool
     */
    protected $hasMarginTop = true;

    /**
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
     * Sets the width of this image in percentage of the line, which contains this image.
     * This function does not touch the real image. It just sets $this->scale, for calculation reasons.
     * @param float $percentageWidth
     */
    public function setPercentageWidth(float $percentageWidth) {
        if ($percentageWidth < 0) {
            throw new \InvalidArgumentException('A negative image width is not allowed.');
        }

        $this->percentageWidth = $percentageWidth;
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
     * Returns the width in percent of the wrapping line.
     * This property is only set when a line is rendered to a template.
     * @return float
     */
    public function getPercentageWidth(): float {
        return $this->percentageWidth;
    }

    /**
     * Returns the margin of this image of percent of the width of one image line.
     * @throws InvalidConfigurationException
     * @return float
     */
    public static function getPercentageMargin() : float {
        return static::getMargin() / ImageLine::getOptimizedWidth() * 100;
    }

    /**
     * This function is called when this object should be rendered to a template.
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|string
     */
    public function forTemplate() : DBHTMLText {
        return $this->renderWith(self::class);
    }
}