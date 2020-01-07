<a href="$Image.URL" title="<%t PaulSchulz\SilverStripe\Gallery\Views\GalleryImage.TOOLTIP "Show image" %>" style="width: <% if $PercentageWidth %>$PercentageWidth\%<% else %>$ScaledWidth\px<% end_if %>; <% if $HasMarginTop %>margin-top: $PercentageMargin\%;<% end_if %> <% if $HasMarginRight %>margin-right: $PercentageMargin\%;<% end_if %>">
    <img src="$Image.URL" alt="$Image.Filename">
</a>