<div class="card w-100 mb-4 info-on-hover choosable-option <% if $Value == 1 %>picked<% end_if %>" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="card-body">
        <h5 class="card-title">{$Title}</h5>
    <% if $Text %>
        <span class="fa fa-info-circle p-absolute t-0 l-0 ml-1 mt-1"></span>
    <% end_if %>
        <span class="fa fa-check p-absolute b-0 r-0 mr-1 mb-1 text-lg text-white"></span>
        <input type="hidden" name="StepOptions[{$ID}]" value="{$Value}" />
    </div>
</div>