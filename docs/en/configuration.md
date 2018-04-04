# Configuration
Configuration are the values you set in your _.yml_ files or directly in your PHP code.  
This describes the configuration values you can set sorted by classes.  
For an example see the _config.yml_, which contains the default values.

## ImageLine ##
 - _optimized_width_ (default: 1920)  
 This is the width an image line (one line in a gallery with images) is optimized for in the PHP code.
 - _desired_height_ (default: 200)
 This is the desired height of one image line. This height can change because all image lines should have the same length. The PHP code calculates which images are put in which lines, so that the height of the resulting image lines is nearly the desired height.

## GalleryImage ##
 - _margin_ (default: 10) The margin added to the images top and right. The images in the first line will not have a margin at the top applied. Every last image in each line has no margin at the right side. This property can also be set to 0, to disable margins.