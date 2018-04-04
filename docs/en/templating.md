# Templating
When overriding templates please make sure to require the file _gallery.css_, if this was done in the already existing template. Otherwise the set of images won't display correctly.

## Templates for DataObjects ##
When rendering your DataObject you have to manually include a template for an image collection or a gallery.
There are two template for image collections and galleries (_Gallery.ss_ and _ImageCollection.ss_). But the gallery template includes the image collection template.