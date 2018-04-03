<% require themedCSS('gallery') %>
<% if $Content %>
    <article>
        $Content
    </article>
<% end_if %>
<% if $ShownGalleries %>
    <section class="galleries">
        <ul>
            <% loop $ShownGalleries %>
                <li>
                    <% include PaulSchulz\SilverStripe\Gallery\Pages\GalleryPreview %>
                </li>
            <% end_loop %>
        </ul>
    </section>
<% end_if %>