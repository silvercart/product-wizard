<% if $Title || $Content %>
<div class="w-100 mt-40 {$ExtraClasses}">
    <% if $Title %>
    <h2>{$Title}</h2>
    <% end_if %>
    <% if $Content %>
        {$Content}
    <% end_if %>
    <% if $Text %>
    <hr>
    <p class="mb-0 text-center">{$Text}</p>
    <% end_if %>
</div>
<% end_if %>