<a href="$URL" title="<%t PaulSchulz\SilverStripe\Gallery\Models\GalleryImage.TOOLTIP "Show image" %>">
    <img src="$URL" alt="$Filename" style="width: <% if $PercentageWidth %>$PercentageWidth\%<% else %>$ScaledWidth\px<% end_if %>; <% if $HasMarginTop %>margin-top: $PercentageMargin\%;<% end_if %> <% if $HasMarginRight %>margin-right: $PercentageMargin\%;<% end_if %>">
</a>