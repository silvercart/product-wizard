<% if $Title || $Content %>
<div class="w-100 p-10 shadow mt-40 {$ExtraClasses}" data-wizard-option-type="{$OptionType}">
    <% if $Title %>
    <h2>{$Title}</h2>
    <% end_if %>
    <% if $Content %>
        {$Content}
    <% end_if %>
    <div class="text-right">
        <a href="{$ButtonTarget}" class="btn btn-outline-primary" {$ButtonTargetTypeAttr}>{$ButtonTitle}</a>
    </div>
</div>
<% else %>
<div class="w-100 text-right {$ExtraClasses}" data-wizard-option-type="{$OptionType}">
    <a href="{$ButtonTarget}" class="btn btn-outline-primary" {$ButtonTargetTypeAttr}>{$ButtonTitle}</a>
</div>
<% end_if %>