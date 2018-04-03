<?php

namespace PaulSchulz\SilverStripe\Gallery\Views;

use PaulSchulz\SilverStripe\Gallery\Exceptions\IllegalStateException;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData;

/**
 * This class represents an image line collection, which is just all lines of an image collection.
 * @package PaulSchulz\SilverStripe\GalleryExtension\Views
 */
class ImageLineCollection extends ViewableData {
    /**
     * @var ArrayList
     */
    protected $lines;

    /**
     * @var string
     */
    protected $biasMode;

    const BIAS_MODE_AVG = 'avg';
    const BIAS_MODE_MAX = 'max';

    /**
     * ImageLineCollection constructor.
     * @param ArrayList $lines
     * @param string biasMode
     */
    public function __construct(ArrayList $lines, string $biasMode) {
        $this->lines = $lines;

        switch ($biasMode) {
            case static::BIAS_MODE_AVG:
            case static::BIAS_MODE_MAX:
                $this->biasMode = $biasMode;
                break;
            default:
                throw new \InvalidArgumentException('Bias mode \'' . $biasMode . '\' does not exist.');
        }
    }

    /**
     * Adds a line to this LineCollection. It adds the line to the start of this list if $atStart is true.
     * @param ImageLine $line
     * @param bool $atStart
     */
    public function addLine(ImageLine $line, bool $atStart = false) {
        if ($atStart) {
            $this->lines->unshift($line);
        }
        else {
            $this->lines->add($line);
        }
    }

    /**
     * This function calculates the deviation to the desired height of all lines of this collection.
     * There are two algorithms (modes):
     *  - avg (This keeps attention on the average difference to the desired height)
     *  - max (This searches for the maximum difference to the desired height of a line and takes this to fi[nd the best order. This mode better prevents very large lines.)
     * @throws \PaulSchulz\SilverStripe\Gallery\Exceptions\InvalidConfigurationException
     * @return float
     */
    public function getBias() : float {
        if ($this->lines->count() === 0) {
            throw new IllegalStateException('The bias of an empty ImageLineCollection instance can not be calculated.');
        }

        $bias = 0;

        switch ($this->biasMode) {
            case static::BIAS_MODE_AVG:
                foreach ($this->lines as $line) {
                    /** @var ImageLine $line */
                    $bias += $line->getBiasFromDesiredHeight();
                }

                return $bias / $this->lines->count();
            case static::BIAS_MODE_MAX:
                foreach ($this->lines as $line) {
                    /** @var ImageLine $line */
                    if (($currentBias = $line->getBiasFromDesiredHeight()) > $bias) {
                        $bias = $currentBias;
                    }
                }

                return $bias;
        }

        throw new \InvalidArgumentException('Bias mode \'' . $this->biasMode . '\' does not exist.');
    }

    /**
     * Returns all lines of this image line collection.
     * @return ArrayList
     */
    public function getLines(): ArrayList {
        return $this->lines;
    }

    /**
     * This function is called when this object should be rendered to a template.
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function forTemplate() {
        return $this->renderWith(self::class);
    }
}