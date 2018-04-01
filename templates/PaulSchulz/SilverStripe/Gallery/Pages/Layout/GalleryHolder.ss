<% if $Content %>
    <article>
        $Content
    </article>
<% end_if %>
<% if $ShownGalleries %>
    <section class="galleries">
        <% loop $ShownGalleries %>
            <article>
                <h1><a href="$Link" title="<%t PaulSchulz\SilverStripe\Gallery\Pages\GalleryHolder.SHOW_GALLERY_TOOLTIP "To the gallery page" %>">$Title</a></h1>
                <% if $Date %>
                    <small class="gallery-date">$Date</small>
                <% end_if %>
                <% if $Location %>
                    <small class="gallery-location">$Location</small>
                <% end_if %>
                <a href="$Link" title="<%t PaulSchulz\SilverStripe\Gallery\Pages\GalleryHolder.SHOW_GALLERY_TOOLTIP "To the gallery page" %>"><img src="$PreviewImage.URL" alt="$PreviewImage.Filename"></a>
            </article>
        <% end_loop %>
    </section>
<% end_if %>