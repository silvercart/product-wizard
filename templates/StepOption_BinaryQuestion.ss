<div class="panel panel-default w-100 my-10 info-on-hover choosable-option <% if $Value == 1 %>picked<% end_if %>" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center">
        <h3 class="panel-title">{$Title}</h3>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
    <span class="fa fa-check p-absolute b-0 r-0 mr-20 mb-20 text-lg text-white"></span>
    <input type="hidden" name="StepOptions[{$ID}]" value="{$Value}" />
</div>