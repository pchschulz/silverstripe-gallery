<% if $Content || $Date || $Location %>
    <article>
        <% if $Date %>
            <small class="gallery-date">$Date</small>
        <% end_if %>
        <% if $Location %>
            <small class="gallery-location">$Location</small>
        <% end_if %>
        $Content
    </article>
<% end_if %>
<% include PaulSchulz\SilverStripe\Gallery\Extensions\ImageCollection %>