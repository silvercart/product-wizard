<% if $Title || $Content %>
<div class="w-100 p-10 shadow mt-20">
    <h2>{$Title}</h2>
    {$Content}
    <div class="text-right">
        <a href="{$ButtonTarget}" class="btn btn-outline-primary" {$ButtonTargetTypeAttr}>{$ButtonTitle}</a>
    </div>
</div>
<% else %>
<div class="w-100 text-right">
    <a href="{$ButtonTarget}" class="btn btn-outline-primary" {$ButtonTargetTypeAttr}>{$ButtonTitle}</a>
</div>
<% end_if %>