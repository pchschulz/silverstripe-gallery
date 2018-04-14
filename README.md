# SilverStripe Gallery Module
This module provides the possibility to easily add a gallery to any DataObject.  
Furthermore this module provides an already finished gallery page for adding galleries to the site tree.

![A gallery with images of cities](docs/en/images/gallery.png)

## Requirements ##
 - PHP 7.0+
 - SilverStripe 4.1+.

## Installation ##
Install via composer: `composer require pchschulz/silverstripe-gallery`

## Usage ##
### For a developer ###
You can add an image gallery to a DataObject, by adding the ImageCollectionExtension or the GalleryExtension to you class.
The gallery page type is already available after installing this module.
[Click here for details](docs/en/index.md)

### For a user ###
Usage for the user is explained [here](docs/en/user-guide.md)

## Translations ##
This module has some translations by default. They can be found under _lang/_. If you want to create further translations you can create further language files. F.e. _fr.yml_ for French.  
For committing your language files to this repository, please create a fork of this project add your files and create a pull request.  
Translations are welcome.

## Bugs ##
If you find any bugs (so-called currently unknown features), you can create an issue on github. You can create a fork again and create a pull request afterwards.