# Creating a DataObject with a gallery
By adding the ImageCollectionExtension or the GalleryExtension to a DataObject subclass you can add a gallery to your DataObject.

## Difference ImageCollectionExtension / GalleryExtension ##
An image collection is just a set of images displayed in a nice form. So the user has not the possibility to add further information like title description or location.
A gallery is an image collection including this further information:
 - __Title__
 - __Date__ (The date the images in the gallery was taken)
 - __Location__ (The place of the images)
 - __Description__ or __Content__
So use the ImageCollectionExtension when you only want to have images displayed in a nice form.
Or choose the GalleryExtension when you want a "real" image gallery, as the GalleryPage is.

## Adding the extension ##
Just add the extension to your DataObject in your _.yml_ files or directly in your PHP code.

## Configuration ##
You have to set set configuration value _optimized_width_ and _desired_height_ for each class that uses one of these extensions.
See [Configuration](configuration.md) for details.

## CMS fields ##
Both extensions add a tab named 'Gallery' to the cms fields.  
ImageCollectionExtension adds the following cms fields:
 - Dropdown field for choosing the bias mode
 - Sortable upload field for images
GalleryExtension adds the following cms fields:
 - Date field
 - Field for the location